<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'name', 'email', 'password', 'role', 'department_id'
    ];

    protected $hidden = [
        'password', 'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];

    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    public function headedDepartment()
    {
        return $this->hasOne(Department::class, 'head_id');
    }

    public function tasks()
    {
        return $this->hasMany(Task::class, 'assigned_to');
    }

    public function attendances()
    {
        return $this->hasMany(Attendance::class);
    }

    public function evaluations()
    {
        return $this->hasMany(Evaluation::class, 'evaluated_user_id');
    }

    public function overtimeRecords()
    {
        return $this->hasMany(OvertimeRecord::class);
    }

    // 🆕 Relations pour les rapports d'évaluation
    public function createdEvaluationReports()
    {
        return $this->hasMany(EvaluationReport::class, 'created_by');
    }

    public function reviewedEvaluationReports()
    {
        return $this->hasMany(EvaluationReport::class, 'reviewed_by');
    }

    // 🆕 Relations pour la gestion de paie
    public function salaries()
    {
        return $this->hasMany(EmployeeSalary::class);
    }

    public function payrollRecords()
    {
        return $this->hasMany(PayrollRecord::class);
    }

    // 🆕 Méthodes utilitaires pour les rapports d'évaluation
    public function canCreateEvaluationReports()
    {
        return $this->role === 'department_head' && $this->department_id;
    }

    public function canReviewEvaluationReports()
    {
        return $this->role === 'hr_admin';
    }

    public function getPendingEvaluationReportsToReview()
    {
        if (!$this->canReviewEvaluationReports()) {
            return collect();
        }

        return EvaluationReport::where('status', 'sent')
                              ->with(['department', 'creator'])
                              ->orderBy('sent_at', 'asc')
                              ->get();
    }

    public function getDraftEvaluationReportsCount()
    {
        if (!$this->canCreateEvaluationReports()) {
            return 0;
        }

        return $this->createdEvaluationReports()
                   ->where('status', 'draft')
                   ->count();
    }

    public function getSentEvaluationReportsCount()
    {
        if (!$this->canCreateEvaluationReports()) {
            return 0;
        }

        return $this->createdEvaluationReports()
                   ->where('status', 'sent')
                   ->count();
    }

    // 🆕 Méthodes utilitaires pour la gestion de paie
    public function getCurrentSalary()
    {
        return $this->salaries()->current()->first();
    }

    public function hasActiveSalary()
    {
        return $this->salaries()->current()->exists();
    }

    public function getLatestPayrollRecord()
    {
        return $this->payrollRecords()
                   ->orderBy('period', 'desc')
                   ->first();
    }

    public function getPayrollRecordsForYear($year = null)
    {
        $year = $year ?? now()->year;
        
        return $this->payrollRecords()
                   ->whereYear('period_start', $year)
                   ->orderBy('period', 'desc')
                   ->get();
    }

    public function getTotalEarningsForYear($year = null)
    {
        $year = $year ?? now()->year;
        
        return $this->payrollRecords()
                   ->whereYear('period_start', $year)
                   ->where('status', 'paid')
                   ->sum('net_salary');
    }

    public function getAveragePerformanceScore($months = 12)
    {
        return $this->payrollRecords()
                   ->where('period_start', '>=', now()->subMonths($months))
                   ->whereNotNull('performance_data')
                   ->get()
                   ->avg(function($record) {
                       return $record->performance_data['overall_score'] ?? 0;
                   });
    }

    // 🆕 Méthodes utilitaires pour les permissions de paie
    public function canViewPayroll()
    {
        return in_array($this->role, ['employee', 'department_head', 'hr_admin']);
    }

    public function canManagePayroll()
    {
        return $this->role === 'hr_admin';
    }

    public function canManageSalaries()
    {
        return $this->role === 'hr_admin';
    }

    public function canApprovePayroll()
    {
        return $this->role === 'hr_admin';
    }

    // 🆕 Accesseurs pour l'affichage
    public function getFullNameAttribute()
    {
        return $this->name;
    }

    public function getRoleDisplayAttribute()
    {
        $roles = [
            'employee' => 'Employé',
            'department_head' => 'Chef de Département',
            'hr_admin' => 'Administrateur RH'
        ];

        return $roles[$this->role] ?? $this->role;
    }

    public function getDepartmentNameAttribute()
    {
        return $this->department ? $this->department->name : 'Aucun département';
    }

    // 🆕 Scopes pour les requêtes
    public function scopeEmployees($query)
    {
        return $query->where('role', 'employee');
    }

    public function scopeDepartmentHeads($query)
    {
        return $query->where('role', 'department_head');
    }

    public function scopeHRAdmins($query)
    {
        return $query->where('role', 'hr_admin');
    }

    public function scopeWithActiveSalary($query)
    {
        return $query->whereHas('salaries', function($q) {
            $q->current();
        });
    }

    public function scopeWithoutActiveSalary($query)
    {
        return $query->whereDoesntHave('salaries', function($q) {
            $q->current();
        });
    }

    public function scopeInDepartment($query, $departmentId)
    {
        return $query->where('department_id', $departmentId);
    }
    // Ajouter ces méthodes dans le Model User existant (app/Models/User.php)

// 🆕 Relations avec les objectifs
public function assignedObjectives()
{
    return $this->hasMany(Objective::class, 'department_id', 'department_id');
}

public function createdObjectives()
{
    return $this->hasMany(Objective::class, 'created_by');
}

// 🆕 Méthodes utilitaires pour les objectifs
public function getActiveObjectivesCount()
{
    if (!$this->department_id) return 0;
    
    return Objective::forDepartment($this->department_id)->active()->count();
}

public function getCompletedObjectivesThisMonthCount()
{
    if (!$this->department_id) return 0;
    
    return Objective::forDepartment($this->department_id)
                   ->completed()
                   ->whereMonth('completed_at', now()->month)
                   ->whereYear('completed_at', now()->year)
                   ->count();
}

public function getOverdueObjectivesCount()
{
    if (!$this->department_id) return 0;
    
    return Objective::forDepartment($this->department_id)->overdue()->count();
}

public function getNewObjectivesCount()
{
    if (!$this->department_id) return 0;
    
    return Objective::forDepartment($this->department_id)
                   ->where('status', 'assigned')
                   ->where('created_at', '>=', now()->subDays(7))
                   ->count();
}

public function getCriticalObjectivesCount()
{
    if (!$this->department_id) return 0;
    
    return Objective::forDepartment($this->department_id)
                   ->critical()
                   ->active()
                   ->count();
}

public function getObjectiveCompletionRate()
{
    if (!$this->department_id) return 0;
    
    $total = Objective::forDepartment($this->department_id)->count();
    if ($total === 0) return 0;
    
    $completed = $this->getCompletedObjectivesThisMonthCount();
    return round(($completed / $total) * 100, 1);
}

public function hasUrgentObjectives()
{
    if (!$this->department_id) return false;
    
    return Objective::forDepartment($this->department_id)
                   ->active()
                   ->where('due_date', '<=', now()->addDays(3))
                   ->exists();
}

public function getObjectiveProgressAverage()
{
    if (!$this->department_id) return 0;
    
    $objectives = Objective::forDepartment($this->department_id)->get();
    
    if ($objectives->isEmpty()) return 0;
    
    return round($objectives->avg('progress_percentage'), 1);
}
}