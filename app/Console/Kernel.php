<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     */
    protected function schedule(Schedule $schedule): void
    {
        // Archiver les comptes bloqués dont la date de début de blocage est échue (tous les jours à minuit)
        $schedule->job(new \App\Jobs\ArchiveExpiredBlockedAccounts)->daily();

        // Débloquer les comptes dont la date de fin de blocage est échue (tous les jours à minuit)
        $schedule->job(new \App\Jobs\UnblockExpiredAccounts)->daily();
    }

    /**
     * Register the commands for the application.
     */
    protected function commands(): void
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
