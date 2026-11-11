<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::command('log:check-errors')->dailyAt('08:00');

Schedule::command('bot:simulate-life --events=1 --subscribers=3 --messages=2 --delay=0 --fresh-users=10')
    ->dailyAt('02:00')
    ->when(fn() => random_int(1, 100) <= 70);