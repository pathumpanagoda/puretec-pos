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
        // Check subscription status daily at 6 AM
        // - Generates subscriptions for current month
        // - Marks overdue subscriptions
        // - Auto-locks tenants with overdue payments (runs on last day of month)
        $schedule->command('subscriptions:check --generate --lock')
            ->dailyAt('06:00')
            ->withoutOverlapping();
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
