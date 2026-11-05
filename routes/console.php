<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Limpieza diaria de lotes de cola; el Scheduler se ejecuta con `php artisan schedule:work`.
Schedule::command('queue:prune-batches')->daily();
