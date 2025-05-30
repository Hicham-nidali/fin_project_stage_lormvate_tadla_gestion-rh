<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Department;
use App\Models\Task;
use App\Models\Attendance;
use App\Models\Request;
use App\Models\Evaluation;
use App\Models\Message;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;

class HRAdminSeeder extends Seeder
{
    public function run()
    {
        echo "🚀 Initialisation du système d'administration RH...\n";

        // 1. Créer les départements
        $departments = [
            [
                'name' => 'Ressources Humaines',
                'description' => 'Département de gestion des ressources humaines et administration'
            ],
            [
                'name' => 'Informatique',
                'description' => 'Département des technologies de l\'information et développement'
            ],
            [
                'name' => 'Finance',
                'description' => 'Département financier et comptabilité'
            ],
            [
                'name' => 'Marketing',
                'description' => 'Département marketing et communication'
            ],
            [
                'name' => 'Commercial',
                'description' => 'Département commercial et ventes'
            ]
        ];

        $createdDepartments = [];
        foreach ($departments as $deptData) {
            $dept = Department::updateOrCreate(
                ['name' => $deptData['name']],
                $deptData
            );
            $createdDepartments[] = $dept;
            echo "✅ Département créé: {$dept->name}\n";
        }

        // 2. Créer l'administrateur RH principal
        $hrAdmin = User::updateOrCreate(
            ['email' => 'admin.rh@entreprise.com'],
            [
                'name' => 'Admin RH Principal',
                'password' => Hash::make('password123'),
                'role' => 'hr_admin',
                'department_id' => $createdDepartments[0]->id // RH
            ]
        );
        echo "✅ Admin RH créé: {$hrAdmin->email}\n";

        // 3. Créer les chefs de département
        $departmentHeads = [
            [
                'name' => 'Chef Informatique',
                'email' => 'chef.it@entreprise.com',
                'department' => 'Informatique'
            ],
            [
                'name' => 'Chef Finance', 
                'email' => 'chef.finance@entreprise.com',
                'department' => 'Finance'
            ],
            [
                'name' => 'Chef Marketing',
                'email' => 'chef.marketing@entreprise.com', 
                'department' => 'Marketing'
            ],
            [
                'name' => 'Chef Commercial',
                'email' => 'chef.commercial@entreprise.com',
                'department' => 'Commercial'
            ]
        ];

        foreach ($departmentHeads as $headData) {
            $department = Department::where('name', $headData['department'])->first();
            
            $head = User::updateOrCreate(
                ['email' => $headData['email']],
                [
                    'name' => $headData['name'],
                    'password' => Hash::make('password123'),
                    'role' => 'department_head',
                    'department_id' => $department->id
                ]
            );

            // Assigner comme chef du département
            $department->update(['head_id' => $head->id]);
            echo "✅ Chef créé: {$head->name} pour {$department->name}\n";
        }

        // 4. Créer les employés
        $employees = [
            // IT
            ['name' => 'Jean Dupont', 'email' => 'jean.dupont@entreprise.com', 'dept' => 'Informatique'],
            ['name' => 'Marie Martin', 'email' => 'marie.martin@entreprise.com', 'dept' => 'Informatique'],
            ['name' => 'Pierre Durand', 'email' => 'pierre.durand@entreprise.com', 'dept' => 'Informatique'],
            
            // Finance
            ['name' => 'Sophie Lefebvre', 'email' => 'sophie.lefebvre@entreprise.com', 'dept' => 'Finance'],
            ['name' => 'Thomas Bernard', 'email' => 'thomas.bernard@entreprise.com', 'dept' => 'Finance'],
            
            // Marketing
            ['name' => 'Alice Rousseau', 'email' => 'alice.rousseau@entreprise.com', 'dept' => 'Marketing'],
            ['name' => 'Lucas Moreau', 'email' => 'lucas.moreau@entreprise.com', 'dept' => 'Marketing'],
            
            // Commercial
            ['name' => 'Emma Dubois', 'email' => 'emma.dubois@entreprise.com', 'dept' => 'Commercial'],
            ['name' => 'Hugo Petit', 'email' => 'hugo.petit@entreprise.com', 'dept' => 'Commercial']
        ];

        foreach ($employees as $empData) {
            $department = Department::where('name', $empData['dept'])->first();
            
            $employee = User::updateOrCreate(
                ['email' => $empData['email']],
                [
                    'name' => $empData['name'],
                    'password' => Hash::make('password123'),
                    'role' => 'employee',
                    'department_id' => $department->id
                ]
            );
            echo "✅ Employé créé: {$employee->name} dans {$department->name}\n";
        }

        // 5. Créer des données d'exemple
        $this->createSampleData();

        echo "\n🎉 SYSTÈME INITIALISÉ AVEC SUCCÈS!\n";
        echo "==========================================\n";
        echo "COMPTES DE CONNEXION:\n";
        echo "==========================================\n";
        echo "👑 Admin RH: admin.rh@entreprise.com / password123\n";
        echo "👨‍💼 Chef IT: chef.it@entreprise.com / password123\n";
        echo "👨‍💼 Chef Finance: chef.finance@entreprise.com / password123\n";
        echo "👨‍💼 Chef Marketing: chef.marketing@entreprise.com / password123\n";
        echo "👨‍💼 Chef Commercial: chef.commercial@entreprise.com / password123\n";
        echo "👤 Employé: jean.dupont@entreprise.com / password123\n";
        echo "👤 Employé: marie.martin@entreprise.com / password123\n";
        echo "==========================================\n";
    }

    private function createSampleData()
    {
        echo "\n📊 Création des données d'exemple...\n";

        // Présences d'exemple
        $employees = User::where('role', 'employee')->take(3)->get();
        foreach ($employees as $employee) {
            for ($i = 0; $i < 5; $i++) {
                $date = Carbon::now()->subDays($i);
                $status = $i == 2 ? 'absent' : ($i == 4 ? 'late' : 'present');
                
                Attendance::updateOrCreate(
                    [
                        'user_id' => $employee->id,
                        'date' => $date->toDateString()
                    ],
                    [
                        'check_in' => $status != 'absent' ? $date->setTime(8, 30) : null,
                        'check_out' => $status != 'absent' ? $date->setTime(17, 30) : null,
                        'status' => $status
                    ]
                );
            }
        }

        // Tâches d'exemple
        $itHead = User::where('email', 'chef.it@entreprise.com')->first();
        $itEmployees = User::where('department_id', $itHead->department_id)
                          ->where('role', 'employee')->get();

        if ($itEmployees->count() > 0) {
            Task::updateOrCreate(
                ['title' => 'Maintenance serveur'],
                [
                    'description' => 'Effectuer la maintenance mensuelle des serveurs',
                    'assigned_to' => $itEmployees->first()->id,
                    'assigned_by' => $itHead->id,
                    'department_id' => $itHead->department_id,
                    'status' => 'completed',
                    'priority' => 'high',
                    'due_date' => Carbon::now()->addDays(3)
                ]
            );

            Task::updateOrCreate(
                ['title' => 'Mise à jour site web'],
                [
                    'description' => 'Mettre à jour le contenu du site web',
                    'assigned_to' => $itEmployees->last()->id,
                    'assigned_by' => $itHead->id,
                    'department_id' => $itHead->department_id,
                    'status' => 'in_progress',
                    'priority' => 'medium',
                    'due_date' => Carbon::now()->addDays(7)
                ]
            );
        }

        // Demandes d'exemple
        if ($itEmployees->count() > 0) {
            Request::updateOrCreate(
                ['title' => 'Demande de congé été'],
                [
                    'description' => 'Congé du 15 au 20 juin pour vacances d\'été',
                    'type' => 'leave',
                    'status' => 'approved',
                    'user_id' => $itEmployees->first()->id,
                    'department_id' => $itHead->department_id
                ]
            );

            Request::updateOrCreate(
                ['title' => 'Remboursement frais mission'],
                [
                    'description' => 'Frais de déplacement client à Paris',
                    'type' => 'expense',
                    'status' => 'pending',
                    'user_id' => $itEmployees->last()->id,
                    'department_id' => $itHead->department_id
                ]
            );
        }

        // Évaluations d'exemple
        if ($itEmployees->count() > 0) {
            Evaluation::updateOrCreate(
                [
                    'evaluated_user_id' => $itEmployees->first()->id,
                    'period' => 'Q1 2024'
                ],
                [
                    'evaluator_id' => $itHead->id,
                    'performance_score' => 8,
                    'communication_score' => 7,
                    'teamwork_score' => 9,
                    'innovation_score' => 8,
                    'comments' => 'Excellent travail en général, continue sur cette voie.',
                    'status' => 'published'
                ]
            );
        }

        // Messages d'exemple
        Message::updateOrCreate(
            ['title' => 'Réunion département IT'],
            [
                'content' => 'Réunion mensuelle du département IT ce vendredi à 10h en salle de réunion.',
                'sender_id' => $itHead->id,
                'department_id' => $itHead->department_id,
                'is_announcement' => true
            ]
        );

        echo "✅ Données d'exemple créées\n";
    }
}