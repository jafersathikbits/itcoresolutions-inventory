<?php

namespace App\Http\Controllers;

use App\Models\Report;
use App\Services\ReportService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ReportController extends Controller
{
    public function index()
    {
        $reports = Report::with('generator')
            ->orderByDesc('report_date')
            ->paginate(15);

        return view('reports.index', compact('reports'));
    }

    public function generateNow(Request $request, ReportService $service)
    {
        $user = $request->user();
        $dateYmd = now()->toDateString();

        $service->generateDaily($dateYmd, $user->id, true);

        return redirect()->route('reports.index')
            ->with('success', "Report generated for {$dateYmd} and sent to Telegram (if chat_id set).");
    }

    public function download(Report $report)
    {
        if (!$report->file_path || !Storage::disk('public')->exists($report->file_path)) {
            return redirect()->route('reports.index')->with('error', 'Report file not found.');
        }

        $downloadName = 'Daily-Report-' . $report->report_date?->format('Y-m-d') . '.pdf';

        return Storage::disk('public')->download($report->file_path, $downloadName);
    }
}
