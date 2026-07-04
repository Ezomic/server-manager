<?php

use App\Models\Metric;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::command('servers:mark-offline')->everyMinute();

Schedule::call(fn () => Metric::where('recorded_at', '<', Date::now()->subDays(7))->delete())
    ->daily()
    ->name('prune-old-metrics');
