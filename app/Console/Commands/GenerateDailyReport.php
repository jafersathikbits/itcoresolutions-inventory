<?php

namespace App\Console\Commands;

use App\Services\ReportService;
use Illuminate\Console\Command;

class GenerateDailyReport extends Command
{
    protected $signature = 'reports:generate-daily {date? : Date in Y-m-d, default today}';
    protected $description = 'Generate daily report PDF and store in DB + send to Telegram';

    public function handle(ReportService $service): int
    {
        $date = $this->argument('date') ?: now()->toDateString();

        $report = $service->generateDaily($date, null, true);

        // Ensure clean printing even if report_date is Carbon
        $reportDate = $report->report_date instanceof \Carbon\Carbon
            ? $report->report_date->toDateString()
            : (string) $report->report_date;

        $this->info("Daily report generated: {$reportDate}");

        return Command::SUCCESS;
    }
}
