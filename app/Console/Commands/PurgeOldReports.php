<?php

namespace App\Console\Commands;

use App\Services\ReportService;
use Illuminate\Console\Command;

class PurgeOldReports extends Command
{
    protected $signature = 'reports:purge-old';
    protected $description = 'Delete reports older than 6 months (DB + files)';

    public function handle(ReportService $service): int
    {
        $deleted = $service->purgeOlderThanSixMonths();

        $this->info("Old reports purged: {$deleted}");

        return Command::SUCCESS;
    }
}
