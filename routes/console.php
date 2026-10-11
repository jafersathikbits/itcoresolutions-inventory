<?php

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

/*
|--------------------------------------------------------------------------
| Console Routes
|--------------------------------------------------------------------------
|
| This file is where you may define all of your Closure based console commands.
| Laravel also uses this file to define scheduled tasks in newer versions.
|
*/

Artisan::command('inspire', function () {
    $this->comment(\Illuminate\Foundation\Inspiring::quote());
})->purpose('Display an inspiring quote')->hourly();

/**
 * Scheduler tasks:
 * - Generate report daily
 * - Purge old reports daily
 *
 * On production you MUST add cron:
 * * * * * cd /path/to/project && php artisan schedule:run >> /dev/null 2>&1
 */
Schedule::command('reports:generate-daily')->dailyAt('23:55');
Schedule::command('reports:purge-old')->dailyAt('23:58');
