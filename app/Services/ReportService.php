<?php

namespace App\Services;

use App\Models\Material;
use App\Models\Order;
use App\Models\Report;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class ReportService
{
    public function generateDaily(string $dateYmd, ?int $generatedByUserId = null, bool $sendTelegram = true): Report
    {
        $start = \Carbon\Carbon::createFromFormat('Y-m-d', $dateYmd)->startOfDay();
        $end   = \Carbon\Carbon::createFromFormat('Y-m-d', $dateYmd)->endOfDay();

        $orders = Order::with(['user', 'items.product'])
            ->whereBetween('placed_at', [$start, $end])
            ->orderBy('placed_at')
            ->get();

        $ordersByStatus = [
            'Received' => $orders->where('status', 'Received')->values(),
            'In Progress' => $orders->where('status', 'In Progress')->values(),
            'Completed' => $orders->where('status', 'Completed')->values(),
            'Dispatched' => $orders->where('status', 'Dispatched')->values(),
        ];

        $materials = Material::with('unit')
            ->orderByRaw('(stock <= threshold) DESC')
            ->orderBy('name')
            ->get();

        $lowMaterials = $materials->filter(fn($m) => (float)$m->stock <= (float)$m->threshold)->values();

        $pdf = Pdf::loadView('reports.pdf.daily', [
            'dateYmd' => $dateYmd,
            'ordersByStatus' => $ordersByStatus,
            'materials' => $materials,
            'lowMaterials' => $lowMaterials,
        ])->setPaper('a4', 'portrait');

        $filename = "daily-report-{$dateYmd}.pdf";
        $folder = "reports/{$dateYmd}";
        $relativePath = "{$folder}/{$filename}";
        $absolutePath = Storage::disk('public')->path($relativePath);

        Storage::disk('public')->makeDirectory($folder);
        Storage::disk('public')->put($relativePath, $pdf->output());

        $report = DB::transaction(function () use ($dateYmd, $generatedByUserId, $relativePath) {
            $existing = Report::where('report_date', $dateYmd)->first();
            if ($existing) {
                $existing->file_path = $relativePath;
                $existing->generated_by = $generatedByUserId;
                $existing->save();
                return $existing;
            }

            return Report::create([
                'report_date' => $dateYmd,
                'file_path' => $relativePath,
                'generated_by' => $generatedByUserId,
            ]);
        });

        if ($sendTelegram) {
            $this->sendToTelegramAdmins($absolutePath, $dateYmd);
        }

        return $report;
    }

    public function purgeOlderThanSixMonths(): int
    {
        $cutoffDate = now()->subMonths(6)->startOfDay();
        $oldReports = Report::where('report_date', '<', $cutoffDate->toDateString())->get();

        $deleted = 0;

        foreach ($oldReports as $r) {
            if ($r->file_path && Storage::disk('public')->exists($r->file_path)) {
                Storage::disk('public')->delete($r->file_path);

                $dir = dirname($r->file_path);
                try {
                    $files = Storage::disk('public')->files($dir);
                    if (count($files) === 0) {
                        Storage::disk('public')->deleteDirectory($dir);
                    }
                } catch (\Throwable $e) {
                    // ignore
                }
            }

            $r->delete();
            $deleted++;
        }

        return $deleted;
    }

    private function sendToTelegramAdmins(string $fileAbsolutePath, string $dateYmd): void
    {
        $telegram = app(TelegramService::class);

        $caption = "<b>Daily Report</b>\nDate: <b>{$dateYmd}</b>";

        $recipients = User::role(['Admin', 'Super Admin'])->get();
        foreach ($recipients as $u) {
            if ($u->telegram_chat_id) {
                $telegram->sendDocument($u->telegram_chat_id, $fileAbsolutePath, $caption);
            }
        }
    }
}
