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
        // Envoyer les rappels d'échéance tous les jours à 9h
        $schedule->command('objectives:send-notifications --type=reminders')
                 ->dailyAt('09:00')
                 ->description('Envoi des rappels d\'échéance des objectifs');
        
        // Envoyer les alertes de retard tous les jours à 10h
        $schedule->command('objectives:send-notifications --type=alerts')
                 ->dailyAt('10:00')
                 ->description('Envoi des alertes pour objectifs en retard');
        
        // Rapport hebdomadaire le lundi à 8h
        $schedule->command('objectives:send-notifications --type=reports')
                 ->weeklyOn(1, '08:00')
                 ->description('Envoi du rapport hebdomadaire des objectifs');
        
        // Vérification et mise à jour automatique des statuts en retard
        $schedule->call(function () {
            \App\Models\Objective::whereDate('due_date', '<', now())
                                 ->whereNotIn('status', ['completed', 'cancelled'])
                                 ->update(['status' => 'overdue']);
        })->hourly()->description('Mise à jour automatique des objectifs en retard');
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