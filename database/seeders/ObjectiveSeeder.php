<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Objective;
use App\Models\Department;
use App\Models\User;
use Carbon\Carbon;

class ObjectiveSeeder extends Seeder
{
    public function run()
    {
        echo "🎯 Création des objectifs d'exemple...\n";

        // Récupérer le user Direction et les départements
        $directionUser = User::where('role', 'direction')->first();
        $departments = Department::all();

        if (!$directionUser) {
            echo "❌ Aucun utilisateur Direction trouvé. Créons-en un...\n";
            $directionUser = User::create([
                'name' => 'Direction Générale',
                'email' => 'direction@entreprise.com',
                'password' => bcrypt('password123'),
                'role' => 'direction',
                'department_id' => null
            ]);
        }

        if ($departments->isEmpty()) {
            echo "❌ Aucun département trouvé. Veuillez d'abord exécuter le seeder des départements.\n";
            return;
        }

        // Objectifs d'exemple pour chaque département
        $objectivesData = [
            // Informatique
            [
                'department_name' => 'Informatique',
                'objectives' => [
                    [
                        'title' => 'Migration vers le Cloud',
                        'description' => 'Migrer 80% de nos services vers l\'infrastructure cloud pour améliorer la scalabilité et réduire les coûts d\'infrastructure de 30%.',
                        'type' => 'quarterly',
                        'priority' => 'high',
                        'progress' => 65,
                        'status' => 'in_progress',
                        'metrics' => [
                            ['name' => 'Services migrés', 'target' => 80, 'unit' => '%'],
                            ['name' => 'Réduction des coûts', 'target' => 30, 'unit' => '%'],
                            ['name' => 'Temps de downtime', 'target' => 2, 'unit' => 'heures max']
                        ],
                        'is_critical' => true
                    ],
                    [
                        'title' => 'Mise à jour sécurité',
                        'description' => 'Déployer les dernières mises à jour de sécurité sur tous les systèmes et former l\'équipe aux nouvelles procédures de cybersécurité.',
                        'type' => 'monthly',
                        'priority' => 'critical',
                        'progress' => 30,
                        'status' => 'in_progress',
                        'metrics' => [
                            ['name' => 'Systèmes mis à jour', 'target' => 100, 'unit' => '%'],
                            ['name' => 'Personnel formé', 'target' => 100, 'unit' => '%']
                        ],
                        'is_critical' => true
                    ],
                    [
                        'title' => 'Optimisation bases de données',
                        'description' => 'Optimiser les performances des bases de données principales pour réduire les temps de réponse de 40%.',
                        'type' => 'monthly',
                        'priority' => 'medium',
                        'progress' => 85,
                        'status' => 'in_progress',
                        'metrics' => [
                            ['name' => 'Réduction temps réponse', 'target' => 40, 'unit' => '%'],
                            ['name' => 'Bases optimisées', 'target' => 5, 'unit' => 'nombre']
                        ]
                    ]
                ]
            ],
            
            // Finance
            [
                'department_name' => 'Finance',
                'objectives' => [
                    [
                        'title' => 'Réduction des coûts opérationnels',
                        'description' => 'Identifier et mettre en œuvre des mesures pour réduire les coûts opérationnels de 15% tout en maintenant la qualité de service.',
                        'type' => 'quarterly',
                        'priority' => 'high',
                        'progress' => 45,
                        'status' => 'in_progress',
                        'metrics' => [
                            ['name' => 'Réduction des coûts', 'target' => 15, 'unit' => '%'],
                            ['name' => 'Économies réalisées', 'target' => 50000, 'unit' => '€']
                        ],
                        'is_critical' => true
                    ],
                    [
                        'title' => 'Automatisation comptable',
                        'description' => 'Déployer un nouveau système de comptabilité automatisé pour réduire les erreurs de 90% et accélérer les processus.',
                        'type' => 'monthly',
                        'priority' => 'medium',
                        'progress' => 20,
                        'status' => 'assigned',
                        'metrics' => [
                            ['name' => 'Réduction des erreurs', 'target' => 90, 'unit' => '%'],
                            ['name' => 'Processus automatisés', 'target' => 80, 'unit' => '%']
                        ]
                    ]
                ]
            ],
            
            // Marketing
            [
                'department_name' => 'Marketing',
                'objectives' => [
                    [
                        'title' => 'Campagne digitale Q1',
                        'description' => 'Lancer une campagne marketing digitale pour augmenter la notoriété de marque de 25% et générer 500 nouveaux leads qualifiés.',
                        'type' => 'quarterly',
                        'priority' => 'high',
                        'progress' => 75,
                        'status' => 'in_progress',
                        'metrics' => [
                            ['name' => 'Augmentation notoriété', 'target' => 25, 'unit' => '%'],
                            ['name' => 'Nouveaux leads', 'target' => 500, 'unit' => 'nombre'],
                            ['name' => 'Taux de conversion', 'target' => 15, 'unit' => '%']
                        ]
                    ],
                    [
                        'title' => 'Refonte site web',
                        'description' => 'Refondre complètement le site web de l\'entreprise pour améliorer l\'expérience utilisateur et augmenter le taux de conversion de 30%.',
                        'type' => 'monthly',
                        'priority' => 'medium',
                        'progress' => 100,
                        'status' => 'completed',
                        'metrics' => [
                            ['name' => 'Pages refondues', 'target' => 20, 'unit' => 'nombre'],
                            ['name' => 'Amélioration conversion', 'target' => 30, 'unit' => '%']
                        ]
                    ]
                ]
            ],
            
            // Commercial
            [
                'department_name' => 'Commercial',
                'objectives' => [
                    [
                        'title' => 'Augmentation du CA',
                        'description' => 'Atteindre un chiffre d\'affaires de 2M€ ce trimestre, soit une augmentation de 20% par rapport au trimestre précédent.',
                        'type' => 'quarterly',
                        'priority' => 'critical',
                        'progress' => 60,
                        'status' => 'in_progress',
                        'metrics' => [
                            ['name' => 'Chiffre d\'affaires', 'target' => 2000000, 'unit' => '€'],
                            ['name' => 'Augmentation CA', 'target' => 20, 'unit' => '%'],
                            ['name' => 'Nouveaux clients', 'target' => 50, 'unit' => 'nombre']
                        ],
                        'is_critical' => true
                    ],
                    [
                        'title' => 'Formation équipe commerciale',
                        'description' => 'Former 100% de l\'équipe commerciale aux nouvelles techniques de vente et aux outils CRM pour améliorer le taux de closing.',
                        'type' => 'monthly',
                        'priority' => 'medium',
                        'progress' => 0,
                        'status' => 'assigned',
                        'metrics' => [
                            ['name' => 'Personnel formé', 'target' => 100, 'unit' => '%'],
                            ['name' => 'Amélioration closing', 'target' => 25, 'unit' => '%']
                        ]
                    ]
                ]
            ],
            
            // RH
            [
                'department_name' => 'Ressources Humaines',
                'objectives' => [
                    [
                        'title' => 'Programme de formation',
                        'description' => 'Mettre en place un programme de formation continue pour tous les employés avec un budget de 50K€ et former 90% du personnel.',
                        'type' => 'quarterly',
                        'priority' => 'medium',
                        'progress' => 40,
                        'status' => 'in_progress',
                        'metrics' => [
                            ['name' => 'Personnel formé', 'target' => 90, 'unit' => '%'],
                            ['name' => 'Budget utilisé', 'target' => 50000, 'unit' => '€'],
                            ['name' => 'Satisfaction formation', 'target' => 85, 'unit' => '%']
                        ]
                    ]
                ]
            ]
        ];

        // Créer les objectifs
        foreach ($objectivesData as $deptData) {
            $department = $departments->where('name', $deptData['department_name'])->first();
            
            if (!$department) {
                echo "⚠️ Département '{$deptData['department_name']}' non trouvé, ignoré.\n";
                continue;
            }

            foreach ($deptData['objectives'] as $objData) {
                // Calculer les dates
                $startDate = Carbon::now()->startOfMonth();
                
                switch ($objData['type']) {
                    case 'monthly':
                        $endDate = $startDate->copy()->endOfMonth();
                        break;
                    case 'quarterly':
                        $endDate = $startDate->copy()->addMonths(3)->endOfMonth();
                        break;
                    case 'annual':
                        $endDate = $startDate->copy()->endOfYear();
                        break;
                    default:
                        $endDate = $startDate->copy()->addMonth();
                }

                // Ajuster les dates pour certains objectifs (simuler différentes périodes)
                if ($objData['status'] === 'completed') {
                    $startDate = Carbon::now()->subMonth()->startOfMonth();
                    $endDate = Carbon::now()->subMonth()->endOfMonth();
                } elseif ($objData['progress'] > 50) {
                    $startDate = Carbon::now()->subWeeks(2);
                }

                $objective = Objective::create([
                    'title' => $objData['title'],
                    'description' => $objData['description'],
                    'type' => $objData['type'],
                    'priority' => $objData['priority'],
                    'status' => $objData['status'],
                    'department_id' => $department->id,
                    'created_by' => $directionUser->id,
                    'start_date' => $startDate,
                    'due_date' => $endDate,
                    'progress_percentage' => $objData['progress'],
                    'metrics' => $objData['metrics'],
                    'is_critical' => $objData['is_critical'] ?? false,
                    'notification_sent' => true,
                    'notified_at' => $startDate->copy()->addHour(),
                    'completed_at' => $objData['status'] === 'completed' ? $endDate : null,
                    'completion_notes' => $objData['status'] === 'completed' ? 'Objectif atteint avec succès dans les délais impartis.' : null
                ]);

                echo "✅ Objectif créé: {$objData['title']} pour {$department->name}\n";
            }
        }

        // Créer quelques objectifs en retard pour tester les alertes
        $itDept = $departments->where('name', 'Informatique')->first();
        if ($itDept) {
            Objective::create([
                'title' => 'Maintenance serveurs urgente',
                'description' => 'Effectuer la maintenance critique des serveurs de production pour éviter les pannes.',
                'type' => 'custom',
                'priority' => 'critical',
                'status' => 'overdue',
                'department_id' => $itDept->id,
                'created_by' => $directionUser->id,
                'start_date' => Carbon::now()->subWeeks(3),
                'due_date' => Carbon::now()->subDays(5), // En retard de 5 jours
                'progress_percentage' => 25,
                'metrics' => [
                    ['name' => 'Serveurs maintenus', 'target' => 10, 'unit' => 'nombre'],
                    ['name' => 'Temps d\'arrêt', 'target' => 2, 'unit' => 'heures max']
                ],
                'is_critical' => true,
                'notification_sent' => true,
                'notified_at' => Carbon::now()->subWeeks(3)->addHour()
            ]);

            echo "⚠️ Objectif en retard créé pour tester les alertes\n";
        }

        echo "\n🎉 Objectifs créés avec succès!\n";
        echo "📊 Statistiques:\n";
        echo "- Total objectifs: " . Objective::count() . "\n";
        echo "- Objectifs actifs: " . Objective::active()->count() . "\n";
        echo "- Objectifs terminés: " . Objective::completed()->count() . "\n";
        echo "- Objectifs en retard: " . Objective::overdue()->count() . "\n";
        echo "- Objectifs critiques: " . Objective::critical()->count() . "\n";
    }
}