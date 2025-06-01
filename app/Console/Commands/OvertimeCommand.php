<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\OvertimeRecord;
use App\Models\User;
use App\Models\Department;
use Carbon\Carbon;

class OvertimeCommand extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'overtime:report {department?} {--month=} {--year=}';

    /**
     * The console command description.
     */
    protected $description = 'Générer un rapport des heures supplémentaires par département';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🕐 Génération du rapport des heures supplémentaires...');
        
        // Paramètres
        $departmentName = $this->argument('department');
        $month = $this->option('month') ?? Carbon::now()->month;
        $year = $this->option('year') ?? Carbon::now()->year;
        
        // Période
        $startDate = Carbon::create($year, $month, 1);
        $endDate = $startDate->copy()->endOfMonth();
        
        $this->info("📅 Période: {$startDate->format('d/m/Y')} - {$endDate->format('d/m/Y')}");
        
        // Requête de base
        $query = OvertimeRecord::where('status', 'approved')
            ->whereBetween('overtime_date', [$startDate, $endDate])
            ->with(['user', 'department']);
        
        // Filtrer par département si spécifié
        if ($departmentName) {
            $department = Department::where('name', 'LIKE', "%{$departmentName}%")->first();
            if (!$department) {
                $this->error("❌ Département '{$departmentName}' non trouvé");
                return 1;
            }
            $query->where('department_id', $department->id);
            $this->info("🏢 Département: {$department->name}");
        }
        
        $overtimeRecords = $query->get();
        
        if ($overtimeRecords->isEmpty()) {
            $this->warn('⚠️  Aucune heure supplémentaire trouvée pour cette période');
            return 0;
        }
        
        // Statistiques globales
        $totalHours = $overtimeRecords->sum('hours_approved');
        $totalRequests = $overtimeRecords->count();
        $averageHours = $totalRequests > 0 ? $totalHours / $totalRequests : 0;
        
        $this->newLine();
        $this->info('📊 STATISTIQUES GLOBALES');
        $this->table(
            ['Métrique', 'Valeur'],
            [
                ['Total heures approuvées', number_format($totalHours, 1) . 'h'],
                ['Nombre de demandes', $totalRequests],
                ['Moyenne par demande', number_format($averageHours, 1) . 'h'],
                ['Employés concernés', $overtimeRecords->unique('user_id')->count()],
            ]
        );
        
        // Groupement par département
        $byDepartment = $overtimeRecords->groupBy('department.name');
        
        $this->newLine();
        $this->info('🏢 RÉPARTITION PAR DÉPARTEMENT');
        $departmentData = [];
        foreach ($byDepartment as $deptName => $records) {
            $departmentData[] = [
                $deptName,
                $records->count(),
                number_format($records->sum('hours_approved'), 1) . 'h',
                $records->unique('user_id')->count() . ' employés'
            ];
        }
        
        $this->table(
            ['Département', 'Demandes', 'Total heures', 'Employés'],
            $departmentData
        );
        
        // Top 5 des employés
        $byEmployee = $overtimeRecords->groupBy('user_id')->map(function ($records) {
            return [
                'name' => $records->first()->user->name,
                'department' => $records->first()->department->name,
                'hours' => $records->sum('hours_approved'),
                'requests' => $records->count(),
            ];
        })->sortByDesc('hours')->take(5);
        
        $this->newLine();
        $this->info('👥 TOP 5 EMPLOYÉS (par heures)');
        $topEmployeeData = [];
        foreach ($byEmployee as $employee) {
            $topEmployeeData[] = [
                $employee['name'],
                $employee['department'],
                number_format($employee['hours'], 1) . 'h',
                $employee['requests']
            ];
        }
        
        $this->table(
            ['Employé', 'Département', 'Total heures', 'Demandes'],
            $topEmployeeData
        );
        
        // Répartition par type
        $this->newLine();
        $this->info('📋 RÉPARTITION PAR TYPE');
        $typeData = [];
        $typeStats = $overtimeRecords->groupBy(function ($record) {
            $metadata = json_decode($record->request->description, true);
            return is_array($metadata) ? ($metadata['overtime_type'] ?? 'Non spécifié') : 'Non spécifié';
        });
        
        foreach ($typeStats as $type => $records) {
            $typeData[] = [
                $type,
                $records->count(),
                number_format($records->sum('hours_approved'), 1) . 'h',
                number_format(($records->count() / $totalRequests) * 100, 1) . '%'
            ];
        }
        
        $this->table(
            ['Type', 'Demandes', 'Total heures', 'Pourcentage'],
            $typeData
        );
        
        // Option d'export
        if ($this->confirm('💾 Voulez-vous exporter ce rapport en CSV ?')) {
            $this->exportToCsv($overtimeRecords, $startDate, $endDate);
        }
        
        $this->newLine();
        $this->info('✅ Rapport généré avec succès !');
        
        return 0;
    }
    
    /**
     * Exporter les données en CSV
     */
    private function exportToCsv($records, $startDate, $endDate)
    {
        $filename = storage_path('app/overtime_report_' . $startDate->format('Y_m') . '.csv');
        
        $file = fopen($filename, 'w');
        
        // Headers
        fputcsv($file, [
            'Date',
            'Employé',
            'Département',
            'Heure début',
            'Heure fin',
            'Heures approuvées',
            'Type',
            'Taux',
            'Raison'
        ]);
        
        // Données
        foreach ($records as $record) {
            $metadata = json_decode($record->request->description, true);
            $type = is_array($metadata) ? ($metadata['overtime_type'] ?? 'Non spécifié') : 'Non spécifié';
            $rate = is_array($metadata) ? ($metadata['overtime_rate'] ?? 1.25) : 1.25;
            
            fputcsv($file, [
                $record->overtime_date->format('d/m/Y'),
                $record->user->name,
                $record->department->name,
                Carbon::parse($record->start_time)->format('H:i'),
                Carbon::parse($record->end_time)->format('H:i'),
                $record->hours_approved,
                $type,
                ($rate * 100) . '%',
                $record->reason
            ]);
        }
        
        fclose($file);
        
        $this->info("📄 Rapport exporté dans: {$filename}");
    }
}