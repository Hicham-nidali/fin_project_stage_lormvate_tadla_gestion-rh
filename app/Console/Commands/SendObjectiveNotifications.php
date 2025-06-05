<?php
namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\ObjectiveNotificationService;
use App\Models\Objective;

class SendObjectiveNotifications extends Command
{
    protected $signature = 'objectives:send-notifications {--type=all : Type of notifications to send (all, reminders, alerts, reports)}';
    protected $description = 'Send objective notifications (reminders, alerts, reports)';

    private $notificationService;

    public function __construct(ObjectiveNotificationService $notificationService)
    {
        parent::__construct();
        $this->notificationService = $notificationService;
    }

    public function handle()
    {
        $type = $this->option('type');
        
        $this->info("🎯 Envoi des notifications d'objectifs...");
        $this->newLine();

        switch ($type) {
            case 'reminders':
                $this->sendReminders();
                break;
            case 'alerts':
                $this->sendAlerts();
                break;
            case 'reports':
                $this->sendReports();
                break;
            case 'all':
            default:
                $this->sendReminders();
                $this->sendAlerts();
                $this->sendReports();
                break;
        }

        $this->newLine();
        $this->info("✅ Notifications envoyées avec succès!");
    }

    private function sendReminders()
    {
        $this->info("📅 Envoi des rappels d'échéance...");
        
        $count = $this->notificationService->sendUpcomingDeadlineReminders();
        
        if ($count > 0) {
            $this->line("   → {$count} rappel(s) envoyé(s) pour les échéances proches");
        } else {
            $this->line("   → Aucun rappel à envoyer");
        }
    }

    private function sendAlerts()
    {
        $this->info("⚠️ Envoi des alertes de retard...");
        
        $count = $this->notificationService->sendOverdueObjectiveAlerts();
        
        if ($count > 0) {
            $this->warn("   → {$count} alerte(s) envoyée(s) pour les objectifs en retard");
        } else {
            $this->line("   → Aucune alerte à envoyer");
        }
    }

    private function sendReports()
    {
        $this->info("📊 Envoi du rapport hebdomadaire...");
        
        $stats = $this->notificationService->sendWeeklyObjectiveReport();
        
        $this->line("   → Rapport envoyé avec les statistiques:");
        $this->line("     • Total: {$stats['total']} objectifs");
        $this->line("     • Actifs: {$stats['active']} objectifs");
        $this->line("     • Terminés cette semaine: {$stats['completed_this_week']} objectifs");
        $this->line("     • En retard: {$stats['overdue']} objectifs");
        $this->line("     • Critiques: {$stats['critical']} objectifs");
    }
}