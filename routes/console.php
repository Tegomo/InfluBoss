<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

/*
| Tâches planifiées. Nécessite sur le serveur une entrée cron :
| * * * * * cd /chemin/du/projet && php artisan schedule:run >> /dev/null 2>&1
*/
Schedule::command('contracts:check-expirations')->dailyAt('07:00')->withoutOverlapping();
