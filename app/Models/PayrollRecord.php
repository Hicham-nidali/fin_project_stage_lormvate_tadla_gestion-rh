<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class PayrollRecord extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'department_id',
        'evaluation_report_id',
        'period',
        'period_start',
        'period_end',
        'base_salary',
        'adjustment_percentage',
        'adjustment_amount',
        'overtime_hours',
        'overtime_rate',
        'overtime_amount',
        'gross_salary',
        'deductions',
        'net_salary',
        'performance_data',
        'adjustment_details',
        'notes',
        'status',
        'calculated_at',
        'calculated_by',
        'approved_at',
        'approved_by',
        'paid_at',
        'payment_reference'
    ];

    protected $casts = [
        'period_start' => 'date',
        'period_end' => 'date',
        'base_salary' => 'decimal:2',
        'adjustment_percentage' => 'decimal:2',
        'adjustment_amount' => 'decimal:2',
        'overtime_hours' => 'decimal:2',
        'overtime_rate' => 'decimal:2',
        'overtime_amount' => 'decimal:2',
        'gross_salary' => 'decimal:2',
        'deductions' => 'decimal:2',
        'net_salary' => 'decimal:2',
        'performance_data' => 'array',
        'adjustment_details' => 'array',
        'calculated_at' => 'datetime',
        'approved_at' => 'datetime',
        'paid_at' => 'datetime'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    public function evaluationReport()
    {
        return $this->belongsTo(EvaluationReport::class);
    }

    public function calculator()
    {
        return $this->belongsTo(User::class, 'calculated_by');
    }

    public function approver()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function scopeDraft($query)
    {
        return $query->where('status', 'draft');
    }

    public function scopeCalculated($query)
    {
        return $query->where('status', 'calculated');
    }

    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }

    public function scopePaid($query)
    {
        return $query->where('status', 'paid');
    }

    public function scopeForPeriod($query, $period)
    {
        return $query->where('period', $period);
    }

    public function scopeForUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    public function scopeForDepartment($query, $departmentId)
    {
        return $query->where('department_id', $departmentId);
    }

    public function scopeRecent($query, $months = 12)
    {
        return $query->where('period_start', '>=', now()->subMonths($months));
    }

    public function getFormattedPeriodAttribute()
    {
        return Carbon::createFromFormat('Y-m', $this->period)->format('F Y');
    }

    public function getStatusBadgeAttribute()
    {
        $badges = [
            'draft' => 'bg-secondary',
            'calculated' => 'bg-primary',
            'approved' => 'bg-success',
            'paid' => 'bg-info'
        ];

        return $badges[$this->status] ?? 'bg-secondary';
    }

    public function getStatusLabelAttribute()
    {
        $labels = [
            'draft' => 'Brouillon',
            'calculated' => 'Calculé',
            'approved' => 'Approuvé',
            'paid' => 'Payé'
        ];

        return $labels[$this->status] ?? 'Inconnu';
    }

    public function hasAdjustment()
    {
        return $this->adjustment_percentage != 0;
    }

    public function isPositiveAdjustment()
    {
        return $this->adjustment_percentage > 0;
    }

    public function isNegativeAdjustment()
    {
        return $this->adjustment_percentage < 0;
    }

    public function hasOvertime()
    {
        return $this->overtime_hours > 0;
    }

    public function canBeApproved()
    {
        return $this->status === 'calculated';
    }

    public function canBePaid()
    {
        return $this->status === 'approved';
    }

    public function getFormattedGrossSalaryAttribute()
    {
        return number_format($this->gross_salary, 2) . ' DH'; // Changé de EUR à DH
    }

    public function getFormattedNetSalaryAttribute()
    {
        return number_format($this->net_salary, 2) . ' DH'; // Changé de EUR à DH
    }

    public function getFormattedAdjustmentAttribute()
    {
        $sign = $this->adjustment_percentage >= 0 ? '+' : '';
        return $sign . number_format($this->adjustment_percentage, 1) . '%';
    }

    public function getPerformanceScoreAttribute()
    {
        return $this->performance_data['overall_score'] ?? 0;
    }

    public function getAttendanceRateAttribute()
    {
        return $this->performance_data['attendance_rate'] ?? 0;
    }

    public function getTaskCompletionRateAttribute()
    {
        return $this->performance_data['task_completion_rate'] ?? 0;
    }

    public static function generatePeriod($date = null)
    {
        $date = $date ? Carbon::parse($date) : now();
        return $date->format('Y-m');
    }

    public static function getAvailablePeriods($limit = 24)
    {
        return self::select('period')
                  ->distinct()
                  ->orderBy('period', 'desc')
                  ->limit($limit)
                  ->pluck('period')
                  ->toArray();
    }
}