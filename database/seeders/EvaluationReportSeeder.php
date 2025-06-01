<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\EvaluationReport;
use App\Models\Department;
use App\Models\User;
use Carbon\Carbon;

class EvaluationReportSeeder extends Seeder
{
    public function run()
    {
        // Récupérer tous les départements avec des chefs
        $departments = Department::whereNotNull('head_id')->with('head')->get();
        
        foreach ($departments as $department) {
            // Créer 2-3 rapports d'évaluation pour chaque département
            for ($i = 0; $i < rand(2, 3); $i++) {
                $startDate = Carbon::now()->subMonths($i + 1)->startOfMonth();
                $endDate = Carbon::now()->subMonths($i + 1)->endOfMonth();
                
                // Données factices pour les employés
                $employeesData = $this->generateEmployeesData($department->id, $startDate, $endDate);
                
                $status = ['draft', 'sent', 'reviewed'][rand(0, 2)];
                $sentAt = $status !== 'draft' ? $startDate->copy()->addDays(rand(1, 5)) : null;
                $reviewedAt = $status === 'reviewed' ? $sentAt->copy()->addDays(rand(1, 7)) : null;
                
                EvaluationReport::create([
                    'title' => "Rapport d'évaluation - " . $startDate->format('M Y') . " - " . $department->name,
                    'summary' => $this->generateSummary($department->name, $startDate),
                    'department_id' => $department->id,
                    'created_by' => $department->head_id,
                    'evaluation_period_start' => $startDate,
                    'evaluation_period_end' => $endDate,
                    'attendance_data' => $employeesData['attendance'],
                    'tasks_data' => $employeesData['tasks'],
                    'requests_data' => $employeesData['requests'],
                    'employees_performance' => $employeesData['performance'],
                    'recommendations' => $this->generateRecommendations(),
                    'status' => $status,
                    'sent_at' => $sentAt,
                    'reviewed_by' => $status === 'reviewed' ? User::where('role', 'hr_admin')->first()?->id : null,
                    'reviewed_at' => $reviewedAt,
                    'hr_comments' => $status === 'reviewed' ? $this->generateHRComments() : null,
                ]);
            }
        }
    }
    
    private function generateEmployeesData($departmentId, $startDate, $endDate)
    {
        $employees = User::where('department_id', $departmentId)
                        ->where('role', 'employee')
                        ->get();
        
        $attendanceData = [];
        $tasksData = [];
        $requestsData = [];
        $performanceData = [];
        
        foreach ($employees as $employee) {
            // Générer des données d'attendance fictives
            $totalDays = rand(20, 25);
            $presentDays = rand(18, $totalDays);
            $absentDays = rand(0, 3);
            $lateDays = rand(0, 5);
            $presenceRate = round(($presentDays / $totalDays) * 100, 2);
            
            $attendanceStats = [
                'total_days' => $totalDays,
                'present_days' => $presentDays,
                'absent_days' => $absentDays,
                'late_days' => $lateDays,
                'presence_rate' => $presenceRate,
            ];
            
            // Générer des données de tâches fictives
            $totalTasks = rand(5, 15);
            $completedTasks = rand(3, $totalTasks);
            $pendingTasks = rand(0, 3);
            $inProgressTasks = $totalTasks - $completedTasks - $pendingTasks;
            $completionRate = round(($completedTasks / $totalTasks) * 100, 2);
            
            $tasksStats = [
                'total_tasks' => $totalTasks,
                'completed_tasks' => $completedTasks,
                'pending_tasks' => $pendingTasks,
                'in_progress_tasks' => $inProgressTasks,
                'completion_rate' => $completionRate,
                'avg_completion_time' => rand(2, 8),
            ];
            
            // Générer des données de demandes fictives
            $totalRequests = rand(0, 5);
            $approvedRequests = rand(0, $totalRequests);
            $pendingRequests = rand(0, max(0, $totalRequests - $approvedRequests));
            $rejectedRequests = $totalRequests - $approvedRequests - $pendingRequests;
            
            $requestsStats = [
                'total_requests' => $totalRequests,
                'approved_requests' => $approvedRequests,
                'pending_requests' => $pendingRequests,
                'rejected_requests' => $rejectedRequests,
            ];
            
            // Calculer la performance globale
            $performanceScore = $this->calculatePerformanceScore($attendanceStats, $tasksStats, $requestsStats);
            
            $attendanceData[$employee->id] = $attendanceStats;
            $tasksData[$employee->id] = $tasksStats;
            $requestsData[$employee->id] = $requestsStats;
            $performanceData[$employee->id] = [
                'employee_name' => $employee->name,
                'overall_score' => $performanceScore,
                'attendance_score' => $presenceRate,
                'tasks_score' => $completionRate,
                'requests_score' => $totalRequests > 0 ? round(($approvedRequests / $totalRequests) * 100, 2) : 100,
            ];
        }
        
        return [
            'attendance' => $attendanceData,
            'tasks' => $tasksData,
            'requests' => $requestsData,
            'performance' => $performanceData,
        ];
    }
    
    private function calculatePerformanceScore($attendance, $tasks, $requests)
    {
        $attendanceWeight = 0.4;
        $tasksWeight = 0.5;
        $requestsWeight = 0.1;

        $attendanceScore = $attendance['presence_rate'];
        $tasksScore = $tasks['completion_rate'];
        $requestsScore = $requests['total_requests'] > 0 ? 
            round(($requests['approved_requests'] / $requests['total_requests']) * 100, 2) : 100;

        return round(
            ($attendanceScore * $attendanceWeight) + 
            ($tasksScore * $tasksWeight) + 
            ($requestsScore * $requestsWeight), 
            2
        );
    }
    
    private function generateSummary($departmentName, $startDate)
    {
        $summaries = [
            "Durant le mois de {$startDate->format('F Y')}, le département {$departmentName} a maintenu un niveau de performance satisfaisant. L'équipe a démontré un bon engagement et une capacité d'adaptation remarquable face aux défis rencontrés.",
            
            "Le rapport d'évaluation pour le département {$departmentName} met en évidence des performances globalement positives pour la période de {$startDate->format('F Y')}. Plusieurs employés se sont distingués par leur professionnalisme et leur efficacité.",
            
            "L'analyse des performances du département {$departmentName} pour {$startDate->format('F Y')} révèle une progression constante dans l'atteinte des objectifs fixés. L'esprit d'équipe et la collaboration ont été des facteurs clés de notre réussite.",
        ];
        
        return $summaries[array_rand($summaries)];
    }
    
    private function generateRecommendations()
    {
        $recommendations = [
            "Pour améliorer davantage les performances de l'équipe, je recommande la mise en place de formations complémentaires et un accompagnement personnalisé pour les employés en difficulté. Un plan de développement des compétences pourrait également être bénéfique.",
            
            "Afin d'optimiser les résultats futurs, il serait pertinent d'instaurer des réunions de suivi hebdomadaires et de clarifier les objectifs individuels. Une meilleure communication interne contribuerait également à l'efficacité globale.",
            
            "Je suggère la mise en place d'un système de reconnaissance des performances exceptionnelles et l'organisation d'ateliers de formation pour renforcer les compétences techniques de l'équipe. Un coaching individuel serait également approprié pour certains collaborateurs.",
        ];
        
        return $recommendations[array_rand($recommendations)];
    }
    
    private function generateHRComments()
    {
        $comments = [
            "Ce rapport d'évaluation est complet et bien structuré. Les données présentées sont cohérentes et les recommandations du chef de département sont pertinentes. L'administration RH validera la mise en place des actions proposées lors du prochain trimestre.",
            
            "L'analyse des performances présentée dans ce rapport témoigne d'un bon suivi de l'équipe par le chef de département. Les points d'amélioration identifiés sont justifiés et les solutions proposées sont réalistes. Un plan d'action sera établi en collaboration avec le département.",
            
            "Ce rapport d'évaluation met en évidence tant les forces que les axes d'amélioration du département. Les recommandations formulées sont en adéquation avec la stratégie RH de l'entreprise. Un suivi trimestriel sera mis en place pour accompagner la progression de l'équipe.",
        ];
        
        return $comments[array_rand($comments)];
    }
}