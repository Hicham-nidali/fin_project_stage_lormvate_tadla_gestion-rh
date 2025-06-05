<?php
namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\PayrollCalculationService;
use App\Models\PayrollRecord;
use Carbon\Carbon;

class ProcessMonthlyPayroll extends Command
{
    protected $signature = 'payroll:process {period?} {--department=} {--approve}';
    protected $description = 'Traite automatiquement les bulletins de paie pour une période donnée';

    protected $payrollService;

    public function __construct(PayrollCalculationService $payrollService)
    {
        parent::__construct();
        $this->payrollService = $payrollService;
    }

    public function handle()
    {
        $period = $this->argument('period') ?? PayrollRecord::generatePeriod(now()->subMonth());
        $departmentId = $this->option('department');
        $autoApprove = $this->option('approve');

        $this->info("Traitement des bulletins de paie pour la période: {$period}");

        try {
            // Calculer les bulletins
            if ($departmentId) {
                $results = $this->payrollService->calculatePayrollForDepartment($departmentId, $period);
                $this->info("Traitement pour le département ID: {$departmentId}");
            } else {
                $results = $this->payrollService->calculatePayrollForAllEmployees($period);
                $this->info("Traitement pour tous les employés");
            }

            // Afficher les résultats
            $successCount = count($results['success']);
            $errorCount = count($results['errors'] ?? []);

            $this->info("✅ {$successCount} bulletin(s) calculé(s) avec succès");
            
            if ($errorCount > 0) {
                $this->warn("⚠️ {$errorCount} erreur(s):");
                foreach ($results['errors'] as $error) {
                    $this->error("- {$error['employee']}: {$error['error']}");
                }
            }

            // Approbation automatique si demandée
            if ($autoApprove && $successCount > 0) {
                $this->info("Approbation automatique des bulletins...");
                
                $calculated = PayrollRecord::calculated()->forPeriod($period);
                if ($departmentId) {
                    $calculated->forDepartment($departmentId);
                }
                
                $toApprove = $calculated->get();
                $approved = 0;
                
                foreach ($toApprove as $payroll) {
                    try {
                        $this->payrollService->approvePayroll($payroll->id);
                        $approved++;
                    } catch (\Exception $e) {
                        $this->error("Erreur approbation {$payroll->user->name}: {$e->getMessage()}");
                    }
                }
                
                $this->info("✅ {$approved} bulletin(s) approuvé(s) automatiquement");
            }

            $this->info("Traitement terminé !");

        } catch (\Exception $e) {
            $this->error("Erreur lors du traitement: {$e->getMessage()}");
            return 1;
        }

        return 0;
    }
}