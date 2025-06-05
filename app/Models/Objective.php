<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Objective extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'description', 
        'type',
        'priority',
        'status',
        'department_id',
        'created_by',
        'start_date',
        'due_date',
        'completed_at',
        'completion_notes',
        'progress_percentage',
        'metrics',
        'notes',
        'is_critical',
        'notification_sent',
        'notified_at'
    ];

    protected $casts = [
        'start_date' => 'date',
        'due_date' => 'date', 
        'completed_at' => 'date',
        'metrics' => 'array',
        'is_critical' => 'boolean',
        'notification_sent' => 'boolean',
        'notified_at' => 'datetime',
        'progress_percentage' => 'integer'
    ];

    // Relations
    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->whereIn('status', ['assigned', 'in_progress']);
    }

    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    public function scopeOverdue($query)
    {
        return $query->where('due_date', '<', now())
                    ->whereNotIn('status', ['completed', 'cancelled']);
    }

    public function scopeCritical($query)
    {
        return $query->where('is_critical', true);
    }

    public function scopeForDepartment($query, $departmentId)
    {
        return $query->where('department_id', $departmentId);
    }

    public function scopeCurrentMonth($query)
    {
        return $query->whereMonth('start_date', now()->month)
                    ->whereYear('start_date', now()->year);
    }

    public function scopeByPriority($query, $priority)
    {
        return $query->where('priority', $priority);
    }

    // Accessors & Mutators
    public function getIsOverdueAttribute()
    {
        return $this->due_date < now() && !in_array($this->status, ['completed', 'cancelled']);
    }

    public function getDaysRemainingAttribute()
    {
        return $this->due_date->diffInDays(now(), false);
    }

    public function getDaysUntilDueAttribute()
    {
        if ($this->due_date < now()) {
            return 0;
        }
        return now()->diffInDays($this->due_date);
    }

    public function getStatusBadgeAttribute()
    {
        $badges = [
            'assigned' => ['class' => 'bg-secondary', 'text' => 'Assigné'],
            'in_progress' => ['class' => 'bg-primary', 'text' => 'En cours'],
            'completed' => ['class' => 'bg-success', 'text' => 'Terminé'],
            'cancelled' => ['class' => 'bg-danger', 'text' => 'Annulé'],
            'overdue' => ['class' => 'bg-warning', 'text' => 'En retard']
        ];

        return $badges[$this->status] ?? ['class' => 'bg-light', 'text' => $this->status];
    }

    public function getPriorityBadgeAttribute()
    {
        $badges = [
            'low' => ['class' => 'bg-success', 'text' => 'Faible'],
            'medium' => ['class' => 'bg-warning', 'text' => 'Moyenne'],
            'high' => ['class' => 'bg-danger', 'text' => 'Haute'],
            'critical' => ['class' => 'bg-dark', 'text' => 'Critique']
        ];

        return $badges[$this->priority] ?? ['class' => 'bg-light', 'text' => $this->priority];
    }

    public function getProgressBarColorAttribute()
    {
        if ($this->is_overdue) {
            return 'bg-danger';
        }

        if ($this->progress_percentage >= 80) {
            return 'bg-success';
        } elseif ($this->progress_percentage >= 50) {
            return 'bg-warning';
        } else {
            return 'bg-info';
        }
    }

    // Methods
    public function markAsCompleted($notes = null)
    {
        $this->update([
            'status' => 'completed',
            'completed_at' => now(),
            'completion_notes' => $notes,
            'progress_percentage' => 100
        ]);
    }

    public function updateProgress($percentage, $notes = null)
    {
        $this->update([
            'progress_percentage' => min(100, max(0, $percentage)),
            'status' => $percentage >= 100 ? 'completed' : 'in_progress',
            'notes' => $notes,
            'completed_at' => $percentage >= 100 ? now() : null
        ]);
    }

    public function markAsOverdue()
    {
        if ($this->due_date < now() && !in_array($this->status, ['completed', 'cancelled'])) {
            $this->update(['status' => 'overdue']);
        }
    }

    public function sendNotification()
    {
        // Logic to send notification to department head
        $this->update([
            'notification_sent' => true,
            'notified_at' => now()
        ]);
    }

    // Static methods
    public static function getOverdueObjectives()
    {
        return static::overdue()->get();
    }

    public static function getCriticalObjectives()
    {
        return static::critical()->active()->get();
    }

    public static function getDepartmentObjectives($departmentId, $status = null)
    {
        $query = static::forDepartment($departmentId);
        
        if ($status) {
            $query->where('status', $status);
        }
        
        return $query->orderBy('due_date')->get();
    }

    public static function getMonthlyStats($month = null, $year = null)
    {
        $month = $month ?: now()->month;
        $year = $year ?: now()->year;

        return [
            'total' => static::whereMonth('start_date', $month)->whereYear('start_date', $year)->count(),
            'completed' => static::whereMonth('start_date', $month)->whereYear('start_date', $year)->where('status', 'completed')->count(),
            'in_progress' => static::whereMonth('start_date', $month)->whereYear('start_date', $year)->where('status', 'in_progress')->count(),
            'overdue' => static::whereMonth('start_date', $month)->whereYear('start_date', $year)->where('status', 'overdue')->count(),
        ];
    }

    // Auto-update overdue status
    protected static function booted()
    {
        static::retrieved(function ($objective) {
            $objective->markAsOverdue();
        });
    }
}