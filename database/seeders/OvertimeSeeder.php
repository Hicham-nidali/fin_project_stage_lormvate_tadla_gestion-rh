<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Department;
use App\Models\Request as EmployeeRequest;
use App\Models\OvertimeRecord;
use Carbon\Carbon;

class OvertimeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Récupérer des employés et départements existants
        $employees = User::where('role', 'employee')->get();
        $departments = Department::all();
        
        if ($employees->isEmpty() || $departments->isEmpty()) {
            $this->command->info('Aucun employé ou département trouvé. Veuillez d\'abord exécuter les seeders de base.');
            return;
        }

        // Créer des exemples de demandes d'heures supplémentaires
        foreach ($employees->take(5) as $employee) {
            // Créer 2-4 demandes d'heures supplémentaires par employé
            $requestCount = rand(2, 4);
            
            for ($i = 0; $i < $requestCount; $i++) {
                // Date aléatoire dans les 30 derniers jours
                $overtimeDate = Carbon::now()->subDays(rand(1, 30));
                
                // Heures aléatoires
                $startHour = rand(17, 20); // Entre 17h et 20h
                $endHour = $startHour + rand(2, 6); // 2 à 6 heures supplémentaires
                
                $startTime = $overtimeDate->copy()->setTime($startHour, 0);
                $endTime = $overtimeDate->copy()->setTime($endHour, 0);
                $hoursRequested = $endTime->diffInHours($startTime);
                
                // Types d'heures supplémentaires
                $overtimeTypes = ['planned', 'urgent', 'project'];
                $overtimeType = $overtimeTypes[array_rand($overtimeTypes)];
                
                // Taux de majoration
                $rates = [1.25, 1.5, 2];
                $rate = $rates[array_rand($rates)];
                
                // Statuts possibles
                $statuses = ['pending', 'approved', 'rejected'];
                $status = $statuses[array_rand($statuses)];
                
                // Raisons d'exemple
                $reasons = [
                    'Finalisation du projet client urgent',
                    'Maintenance système en dehors des heures ouvrables',
                    'Préparation de la présentation pour le comité directeur',
                    'Support technique pour un client prioritaire',
                    'Développement de fonctionnalité critique',
                    'Formation d\'équipe en soirée',
                    'Audit de sécurité informatique',
                    'Migration de données importantes'
                ];
                $reason = $reasons[array_rand($reasons)];
                
                // Créer la demande principale
                $employeeRequest = EmployeeRequest::create([
                    'title' => "Heures supplémentaires - " . $overtimeDate->format('d/m/Y'),
                    'description' => json_encode([
                        'overtime_type' => $overtimeType,
                        'overtime_rate' => $rate,
                        'original_description' => $reason
                    ]),
                    'type' => 'overtime',
                    'status' => $status,
                    'user_id' => $employee->id,
                    'department_id' => $employee->department_id,
                    'approved_by' => $status !== 'pending' ? $employee->department->head_id : null,
                    'approved_at' => $status !== 'pending' ? $overtimeDate->copy()->addDays(rand(1, 3)) : null,
                ]);
                
                // Créer l'enregistrement d'heures supplémentaires
                OvertimeRecord::create([
                    'request_id' => $employeeRequest->id,
                    'user_id' => $employee->id,
                    'department_id' => $employee->department_id,
                    'overtime_date' => $overtimeDate->toDateString(),
                    'start_time' => $startTime->format('H:i'),
                    'end_time' => $endTime->format('H:i'),
                    'hours_requested' => $hoursRequested,
                    'hours_approved' => $status === 'approved' ? $hoursRequested : null,
                    'reason' => $reason,
                    'status' => $status,
                    'approved_by' => $status !== 'pending' ? $employee->department->head_id : null,
                    'approved_at' => $status !== 'pending' ? $overtimeDate->copy()->addDays(rand(1, 3)) : null,
                ]);
            }
        }
        
        $this->command->info('Données d\'exemple pour les heures supplémentaires créées avec succès !');
    }
}