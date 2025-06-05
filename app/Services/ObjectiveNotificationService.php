<?php
namespace App\Services;

use App\Models\Objective;
use App\Models\User;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

class ObjectiveNotificationService
{
    /**
     * Envoyer une notification lors de la création d'un objectif
     */
    public function sendObjectiveAssignedNotification(Objective $objective)
    {
        $departmentHead = $objective->department->head;
        
        if (!$departmentHead) {
            Log::warning("Aucun chef de département assigné pour le département {$objective->department->name}");
            return false;
        }

        try {
            // Ici vous pouvez implémenter l'envoi d'email
            // Mail::to($departmentHead->email)->send(new ObjectiveAssignedMail($objective));
            
            // Pour l'instant, on log juste
            Log::info("Notification envoyée pour l'objectif '{$objective->title}' au chef {$departmentHead->name}");
            
            return true;
        } catch (\Exception $e) {
            Log::error("Erreur lors de l'envoi de notification pour l'objectif {$objective->id}: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Envoyer des rappels pour les objectifs à échéance proche
     */
    public function sendUpcomingDeadlineReminders()
    {
        $upcomingObjectives = Objective::active()
            ->where('due_date', '>=', now())
            ->where('due_date', '<=', now()->addDays(3))
            ->with(['department.head'])
            ->get();

        $sentCount = 0;

        foreach ($upcomingObjectives as $objective) {
            if ($objective->department->head) {
                try {
                    // Envoyer le rappel
                    Log::info("Rappel d'échéance envoyé pour l'objectif '{$objective->title}' à {$objective->department->head->name}");
                    $sentCount++;
                } catch (\Exception $e) {
                    Log::error("Erreur lors de l'envoi du rappel pour l'objectif {$objective->id}: " . $e->getMessage());
                }
            }
        }

        return $sentCount;
    }

    /**
     * Envoyer des alertes pour les objectifs en retard
     */
    public function sendOverdueObjectiveAlerts()
    {
        $overdueObjectives = Objective::overdue()
            ->with(['department.head'])
            ->get();

        $sentCount = 0;

        foreach ($overdueObjectives as $objective) {
            if ($objective->department->head) {
                try {
                    // Envoyer l'alerte
                    Log::warning("Alerte de retard envoyée pour l'objectif '{$objective->title}' à {$objective->department->head->name}");
                    $sentCount++;
                } catch (\Exception $e) {
                    Log::error("Erreur lors de l'envoi de l'alerte pour l'objectif {$objective->id}: " . $e->getMessage());
                }
            }
        }

        return $sentCount;
    }

    /**
     * Notifier la direction quand un objectif est terminé
     */
    public function sendObjectiveCompletedNotification(Objective $objective)
    {
        $directionUsers = User::where('role', 'direction')->get();
        
        foreach ($directionUsers as $director) {
            try {
                // Envoyer la notification
                Log::info("Notification de completion envoyée à la direction pour l'objectif '{$objective->title}'");
            } catch (\Exception $e) {
                Log::error("Erreur lors de l'envoi de notification de completion: " . $e->getMessage());
            }
        }
    }

    /**
     * Envoyer un rapport hebdomadaire des objectifs
     */
    public function sendWeeklyObjectiveReport()
    {
        $directionUsers = User::where('role', 'direction')->get();
        
        $stats = [
            'total' => Objective::count(),
            'active' => Objective::active()->count(),
            'completed_this_week' => Objective::completed()
                ->where('completed_at', '>=', now()->startOfWeek())
                ->count(),
            'overdue' => Objective::overdue()->count(),
            'critical' => Objective::critical()->active()->count(),
        ];

        foreach ($directionUsers as $director) {
            try {
                // Envoyer le rapport
                Log::info("Rapport hebdomadaire des objectifs envoyé à {$director->name}");
            } catch (\Exception $e) {
                Log::error("Erreur lors de l'envoi du rapport hebdomadaire: " . $e->getMessage());
            }
        }

        return $stats;
    }
}