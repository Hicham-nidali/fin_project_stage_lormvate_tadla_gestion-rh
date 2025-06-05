<?php
// app/Models/Department.php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Department extends Model
{
    use HasFactory;

    protected $fillable = [
        'name', 'description', 'head_id'
    ];

    public function head()
    {
        return $this->belongsTo(User::class, 'head_id');
    }

    public function users()
    {
        return $this->hasMany(User::class);
    }

    public function tasks()
    {
        return $this->hasMany(Task::class);
    }

    public function reports()
    {
        return $this->hasMany(Report::class);
    }

    // 🆕 Relation avec les demandes
    public function requests()
    {
        return $this->hasMany(Request::class);
    }

    // 🆕 Relation avec les rapports d'évaluation
    public function evaluationReports()
    {
        return $this->hasMany(EvaluationReport::class);
    }

    // 🆕 Relations pour la gestion de paie
    public function employeeSalaries()
    {
        return $this->hasMany(EmployeeSalary::class);
    }

    public function payrollRecords()
    {
        return $this->hasMany(PayrollRecord::class);
    }

    // 🆕 Méthodes utilitaires pour les rapports d'évaluation
    public function getLatestEvaluationReport()
    {
        return $this->evaluationReports()
                   ->where('status', '!=', 'draft')
                   ->latest('sent_at')
                   ->first();
    }

    public function getPendingEvaluationReportsCount()
    {
        return $this->evaluationReports()
                   ->where('status', 'sent')
                   ->count();
    }

    public function getReviewedEvaluationReportsCount()
    {
        return $this->evaluationReports()
                   ->where('status', 'reviewed')
                   ->count();
    }

    // 🆕 Méthodes utilitaires pour la gestion de paie
    public function getActiveEmployeesWithSalary()
    {
        return $this->users()
                   ->where('role', 'employee')
                   ->whereHas('salaries', function($q) {
                       $q->current();
                   });
    }

    public function getEmployeesWithoutSalary()
    {
        return $this->users()
                   ->where('role', 'employee')
                   ->whereDoesntHave('salaries', function($q) {
                       $q->current();
                   });
    }

    public function getTotalMonthlySalaryBudget()
    {
        return $this->employeeSalaries()
                   ->current()
                   ->sum('base_salary');
    }

    public function getPayrollSummaryForPeriod($period)
    {
        $records = $this->payrollRecords()
                       ->forPeriod($period)
                       ->get();

        return [
            'total_employees' => $records->count(),
            'total_gross' => $records->sum('gross_salary'),
            'total_net' => $records->sum('net_salary'),
            'total_adjustments' => $records->sum('adjustment_amount'),
            'total_overtime' => $records->sum('overtime_amount'),
            'avg_performance' => $records->avg(function($record) {
                return $record->performance_data['overall_score'] ?? 0;
            })
        ];
    }

    public function getLatestPayrollPeriod()
    {
        return $this->payrollRecords()
                   ->orderBy('period', 'desc')
                   ->value('period');
    }

    public function getPendingPayrollApprovalsCount()
    {
        return $this->payrollRecords()
                   ->where('status', 'calculated')
                   ->count();
    }

    public function getEmployeeCountByPayrollStatus($period = null)
    {
        $query = $this->payrollRecords();
        
        if ($period) {
            $query->forPeriod($period);
        } else {
            $latestPeriod = $this->getLatestPayrollPeriod();
            if ($latestPeriod) {
                $query->forPeriod($latestPeriod);
            }
        }

        return $query->groupBy('status')
                    ->selectRaw('status, count(*) as count')
                    ->pluck('count', 'status')
                    ->toArray();
    }

    public function getAverageSalaryForRole($role = 'employee')
    {
        return $this->users()
                   ->where('role', $role)
                   ->whereHas('salaries', function($q) {
                       $q->current();
                   })
                   ->with(['salaries' => function($q) {
                       $q->current();
                   }])
                   ->get()
                   ->avg(function($user) {
                       return $user->salaries->first()?->base_salary ?? 0;
                   });
    }

    public function getPayrollCompletionRate($period = null)
    {
        $totalEmployees = $this->users()->where('role', 'employee')->count();
        
        if ($totalEmployees === 0) {
            return 0;
        }

        $query = $this->payrollRecords();
        
        if ($period) {
            $query->forPeriod($period);
        } else {
            $latestPeriod = $this->getLatestPayrollPeriod();
            if ($latestPeriod) {
                $query->forPeriod($latestPeriod);
            }
        }

        $processedPayrolls = $query->count();

        return round(($processedPayrolls / $totalEmployees) * 100, 2);
    }

    // 🆕 Scopes pour les requêtes
    public function scopeWithPayrollData($query, $period = null)
    {
        return $query->with(['payrollRecords' => function($q) use ($period) {
            if ($period) {
                $q->forPeriod($period);
            }
        }]);
    }

    public function scopeWithActiveSalaries($query)
    {
        return $query->with(['employeeSalaries' => function($q) {
            $q->current();
        }]);
    }

    // 🆕 Accesseurs
    public function getEmployeeCountAttribute()
    {
        return $this->users()->where('role', 'employee')->count();
    }

    public function getHeadNameAttribute()
    {
        return $this->head ? $this->head->name : 'Aucun chef assigné';
    }

    public function getTotalBudgetAttribute()
    {
        return $this->getTotalMonthlySalaryBudget();
    }
    // Ajouter cette méthode dans le Model Department existant (app/Models/Department.php)

// 🆕 Relation avec les objectifs
public function objectives()
{
    return $this->hasMany(Objective::class);
}

// 🆕 Méthodes utilitaires pour les objectifs
public function getActiveObjectivesCount()
{
    return $this->objectives()->active()->count();
}

public function getCompletedObjectivesCount()
{
    return $this->objectives()->completed()->count();
}

public function getOverdueObjectivesCount()
{
    return $this->objectives()->overdue()->count();
}

public function getCriticalObjectivesCount()
{
    return $this->objectives()->critical()->active()->count();
}

public function getObjectiveCompletionRate()
{
    $total = $this->objectives()->count();
    if ($total === 0) return 0;
    
    $completed = $this->getCompletedObjectivesCount();
    return round(($completed / $total) * 100, 1);
}

public function getCurrentMonthObjectives()
{
    return $this->objectives()
               ->currentMonth()
               ->orderBy('due_date')
               ->get();
}

public function hasUrgentObjectives()
{
    return $this->objectives()
               ->active()
               ->where('due_date', '<=', now()->addDays(3))
               ->exists();
}
}