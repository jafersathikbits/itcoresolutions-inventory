<?php

namespace App\Http\Controllers;

use App\Models\Material;
use App\Models\Order;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public const STATUSES = ['Received', 'In Progress', 'Completed', 'Dispatched'];

    public function index(Request $request)
    {
        $todayStart = now()->startOfDay();
        $todayEnd   = now()->endOfDay();

        // Orders today: one grouped query instead of five separate COUNT queries
        $countsByStatus = Order::whereBetween('placed_at', [$todayStart, $todayEnd])
            ->selectRaw('status, COUNT(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');

        $statusCounts = [];
        foreach (self::STATUSES as $status) {
            $statusCounts[$status] = (int) ($countsByStatus[$status] ?? 0);
        }
        $ordersTodayTotal = (int) $countsByStatus->sum();

        // Latest orders (all-time)
        $latestOrders = Order::with(['user', 'items.product'])
            ->orderByDesc('placed_at')
            ->limit(10)
            ->get();

        // Low stock materials (most critical first) - fetched once, reused for table + chart
        $lowStockCount = Material::whereColumn('stock', '<=', 'threshold')->count();

        $lowMaterials = Material::with('unit')
            ->whereColumn('stock', '<=', 'threshold')
            ->orderByRaw('(threshold - stock) DESC')
            ->limit(10)
            ->get();

        // Top low materials for chart (stock vs threshold)
        $topLowForChart = $lowMaterials->take(8)
            ->map(fn ($m) => [
                'name' => $m->name,
                'unit' => $m->unit?->symbol ?? '',
                'stock' => (float) $m->stock,
                'threshold' => (float) $m->threshold,
            ])
            ->values()
            ->all();

        return view('dashboard.index', [
            'ordersTodayTotal' => $ordersTodayTotal,
            'statusCounts' => $statusCounts,
            'lowStockCount' => $lowStockCount,
            'latestOrders' => $latestOrders,
            'lowMaterials' => $lowMaterials,
            'donutLabels' => array_keys($statusCounts),
            'donutValues' => array_values($statusCounts),
            'topLowForChart' => $topLowForChart,
        ]);
    }
}
