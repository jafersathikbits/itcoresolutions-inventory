<?php

namespace App\Http\Controllers;

use App\Models\AppNotification;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\User;
use App\Services\OrderStockService;
use App\Services\TelegramService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class OrderController extends Controller
{
    public function index(Request $request)
    {
        $status = $request->query('status');
        $date = $request->query('date');

        $query = Order::with(['user', 'items.product'])->orderByDesc('placed_at');

        // Filter by status
        $allowedStatuses = ['Received', 'In Progress', 'Completed', 'Dispatched'];
        if ($status && in_array($status, $allowedStatuses, true)) {
            $query->where('status', $status);
        }

        // Filter by date: today OR YYYY-MM-DD
        if ($date) {
            if ($date === 'today') {
                $start = now()->startOfDay();
                $end = now()->endOfDay();
                $query->whereBetween('placed_at', [$start, $end]);
            } else {
                // Attempt parse YYYY-MM-DD
                try {
                    $start = \Carbon\Carbon::createFromFormat('Y-m-d', $date)->startOfDay();
                    $end = \Carbon\Carbon::createFromFormat('Y-m-d', $date)->endOfDay();
                    $query->whereBetween('placed_at', [$start, $end]);
                } catch (\Throwable $e) {
                    // ignore invalid date
                }
            }
        }

        $orders = $query->paginate(15)->withQueryString();

        return view('orders.index', [
            'orders' => $orders,
            'filterStatus' => $status,
            'filterDate' => $date,
            'allowedStatuses' => $allowedStatuses,
        ]);
    }

    public function create(OrderStockService $stockService)
    {
        $products = Product::with(['materials.unit', 'materials.components.childMaterial.unit'])
            ->orderBy('name')
            ->get();

        // Per-product raw requirements for 1 unit + current stock, used by the live
        // "stock check" preview on the order form (the server re-checks on submit).
        $requirementsByProduct = $products->mapWithKeys(fn (Product $p) => [
            $p->id => collect($stockService->computeRequirements($p, 1))
                ->map(fn ($row) => [
                    'name' => $row['material']->name,
                    'unit' => $row['material']->unit?->symbol ?? '',
                    'per_unit' => (float) $row['required'],
                    'stock' => (float) $row['material']->stock,
                    'threshold' => (float) $row['material']->threshold,
                ])
                ->sortBy('name')
                ->values(),
        ]);

        return view('orders.create', compact('products', 'requirementsByProduct'));
    }

    public function store(Request $request, OrderStockService $stockService, TelegramService $telegram)
    {
        $data = $request->validate([
            'product_id' => ['required', 'integer', 'exists:products,id'],
            'quantity' => ['required', 'numeric', 'gt:0'],
            'notes' => ['nullable', 'string', 'max:500'],
        ]);

        $user = $request->user();
        $product = Product::findOrFail((int)$data['product_id']);
        $qty = round((float)$data['quantity'], 3);

        // Requirements (expanded)
        $requirements = $stockService->computeRequirements($product, $qty);

        if (count($requirements) === 0) {
            return back()->withInput()->with('error', 'This product has no BOM configured. Please configure BOM first.');
        }

        // Stock check + deduction run in ONE transaction with the material rows locked,
        // so two orders placed at the same moment cannot both consume the same stock.
        $order = null;

        $check = DB::transaction(function () use (&$order, $user, $product, $qty, $data, $requirements, $stockService) {
            $check = $stockService->checkStock($requirements, lockForUpdate: true);

            if ($check['status'] === 'blocked') {
                return $check;
            }

            $order = Order::create([
                // unique placeholder (order_no is UNIQUE) - replaced right below once we have the id
                'order_no' => 'TMP-' . Str::uuid(),
                'placed_by' => $user->id,
                'status' => 'Received',
                'placed_at' => now(),
                'notes' => $data['notes'] ?? null,
            ]);

            $year = now()->format('Y');
            $order->order_no = 'ORD-' . $year . '-' . str_pad((string)$order->id, 6, '0', STR_PAD_LEFT);
            $order->save();

            OrderItem::create([
                'order_id' => $order->id,
                'product_id' => $product->id,
                'quantity' => $qty,
                'bom_snapshot' => [
                    'product_id' => $product->id,
                    'product_name' => $product->name,
                    'quantity' => $qty,
                    'requirements' => array_map(function ($row) {
                        return [
                            'material_id' => $row['material']->id,
                            'material_name' => $row['material']->name,
                            'required' => $row['required'],
                            'unit' => $row['material']->unit?->symbol ?? '',
                        ];
                    }, $requirements),
                ],
            ]);

            $stockService->deductStock(
                requirements: $requirements,
                userId: $user->id,
                orderId: $order->id,
                reason: 'Order placed: ' . $order->order_no
            );

            return $check;
        });

        if ($check['status'] === 'blocked') {
            $this->notifyAdmins(
                type: 'order_blocked',
                title: 'Order Blocked (Insufficient Stock)',
                message: $this->formatBlockedMessage($product, $qty, $check['insufficient']),
                data: [
                    'product_id' => $product->id,
                    'product_name' => $product->name,
                    'quantity' => $qty,
                    'insufficient' => $check['insufficient'],
                ],
                telegram: $telegram
            );

            return back()->withInput()->with('error', 'Order blocked: insufficient stock. Admin/Super Admin have been notified.');
        }

        $warning = ($check['status'] === 'warning');

        if ($warning) {
            $this->notifyAdmins(
                type: 'order_warning',
                title: 'Order Warning (Low Stock After Deduction)',
                message: $this->formatWarningMessage($product, $qty, $check['low_after'], $order?->order_no),
                data: [
                    'order_id' => $order->id,
                    'order_no' => $order->order_no,
                    'product_id' => $product->id,
                    'product_name' => $product->name,
                    'quantity' => $qty,
                    'low_after' => $check['low_after'],
                ],
                telegram: $telegram
            );

            return redirect()->route('orders.index')->with('success', 'Order placed with warning: some materials became LOW. Admin/Super Admin notified.');
        }

        return redirect()->route('orders.index')->with('success', 'Order placed successfully.');
    }

    public function updateStatus(Request $request, Order $order)
    {
        $data = $request->validate([
            'status' => ['required', 'in:Received,In Progress,Completed,Dispatched'],
        ]);

        $order->status = $data['status'];
        $order->save();

        // go back to the same page/filters the user was looking at
        return back()->with('success', "Order {$order->order_no} status updated to {$order->status}.");
    }

    private function notifyAdmins(string $type, string $title, string $message, array $data, TelegramService $telegram): void
    {
        $recipients = User::role(['Admin', 'Super Admin'])->get();

        // $message is Telegram HTML; the in-app notification stores plain text
        // (the notifications page escapes output, so tags would show literally).
        $plainMessage = html_entity_decode(strip_tags($message), ENT_QUOTES | ENT_HTML5);

        foreach ($recipients as $u) {
            AppNotification::create([
                'user_id' => $u->id,
                'type' => $type,
                'title' => $title,
                'message' => $plainMessage,
                'data' => $data,
                'read_at' => null,
            ]);

            if ($u->telegram_chat_id) {
                $telegram->sendMessage($u->telegram_chat_id, $message);
            }
        }
    }

    private function formatBlockedMessage(Product $product, float $qty, array $insufficient): string
    {
        $lines = [];
        $lines[] = "<b>ORDER BLOCKED</b>";
        $lines[] = "Product: <b>" . e($product->name) . "</b>";
        $lines[] = "Qty: <b>{$qty}</b>";
        $lines[] = "";
        $lines[] = "<b>Insufficient materials:</b>";

        foreach ($insufficient as $row) {
            $lines[] = '- ' . e($row['name']) . ' (' . e($row['unit']) . "): need {$row['required']}, stock {$row['stock']}, short {$row['short_by']}";
        }

        return implode("\n", $lines);
    }

    private function formatWarningMessage(Product $product, float $qty, array $lowAfter, ?string $orderNo): string
    {
        $lines = [];
        $lines[] = "<b>ORDER WARNING</b>";
        if ($orderNo) {
            $lines[] = "Order: <b>{$orderNo}</b>";
        }
        $lines[] = "Product: <b>" . e($product->name) . "</b>";
        $lines[] = "Qty: <b>{$qty}</b>";
        $lines[] = "";
        $lines[] = "<b>Materials LOW after deduction:</b>";

        foreach ($lowAfter as $row) {
            $lines[] = '- ' . e($row['name']) . ' (' . e($row['unit']) . "): after {$row['after_stock']} (threshold {$row['threshold']})";
        }

        return implode("\n", $lines);
    }
}
