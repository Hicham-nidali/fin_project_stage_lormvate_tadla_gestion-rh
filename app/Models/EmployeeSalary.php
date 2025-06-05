<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class EmployeeSalary extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'department_id', 
        'base_salary',
        'currency',
        'effective_from',
        'effective_to',
        'notes',
        'created_by'
    ];

    protected $casts = [
        'effective_from' => 'date',
        'effective_to' => 'date',
        'base_salary' => 'decimal:2'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function payrollRecords()
    {
        return $this->hasMany(PayrollRecord::class, 'user_id', 'user_id');
    }

    public function scopeCurrent($query)
    {
        return $query->where('effective_from', '<=', now())
                    ->where(function ($q) {
                        $q->whereNull('effective_to')
                          ->orWhere('effective_to', '>=', now());
                    });
    }

    public function scopeForUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    public function scopeForDepartment($query, $departmentId)
    {
        return $query->where('department_id', $departmentId);
    }

    public function isActive()
    {
        return $this->effective_from <= now() && 
               (is_null($this->effective_to) || $this->effective_to >= now());
    }

    public function isExpired()
    {
        return !is_null($this->effective_to) && $this->effective_to < now();
    }

    public function isFuture()
    {
        return $this->effective_from > now();
    }

    public static function getCurrentSalaryForUser($userId)
    {
        return self::where('user_id', $userId)
                  ->current()
                  ->orderBy('effective_from', 'desc')
                  ->first();
    }

    public static function getSalaryForUserAtDate($userId, $date)
    {
        $date = Carbon::parse($date);
        
        return self::where('user_id', $userId)
                  ->where('effective_from', '<=', $date)
                  ->where(function ($q) use ($date) {
                      $q->whereNull('effective_to')
                        ->orWhere('effective_to', '>=', $date);
                  })
                  ->orderBy('effective_from', 'desc')
                  ->first();
    }

    public function getFormattedSalaryAttribute()
    {
        return number_format($this->base_salary, 2) . ' DH'; // Changé de EUR à DH
    }

    public function getDurationAttribute()
    {
        $start = $this->effective_from;
        $end = $this->effective_to ?? now();
        
        return $start->diffForHumans($end, [
            'parts' => 2,
            'short' => false,
            'syntax' => Carbon::DIFF_ABSOLUTE
        ]);
    }
}