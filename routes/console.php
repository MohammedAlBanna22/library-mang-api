<?php

use App\Models\Borrowing;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;




Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');


Schedule::call(function () {
    Borrowing::markAllOverdue(); // ← same method, no duplication
})->dailyAt('00:00');

Schedule::command('borrowings:mark-overdue')
    ->dailyAt('00:00')
    ->withoutOverlapping();