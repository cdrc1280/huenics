<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Daily warranty status refresh and overdue delivery check at 8:00 AM
Schedule::command('huenics:check-warranties-deliveries')->dailyAt('08:00');
