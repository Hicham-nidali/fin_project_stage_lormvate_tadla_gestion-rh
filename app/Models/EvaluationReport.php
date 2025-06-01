<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EvaluationReport extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'summary',
        'department_id',
        'created_by',
        'evaluation_period_start',
        'evaluation_period_end',
        'attendance_data',
        'tasks_data',
        'requests_data',
        'employees_performance',
        'recommendations',
        'status',
        'sent_at',
        'reviewed_by',
        'reviewed_at',
        'hr_comments'
    ];

    protected $casts = [
        'evaluation_period_start' => 'date',
        'evaluation_period_end' => 'date',
        'attendance_data' => 'array',
        'tasks_data' => 'array',
        'requests_data' => 'array',
        'employees_performance' => 'array',
        'sent_at' => 'datetime',
        'reviewed_at' => 'datetime',
    ];

    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function reviewer()
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    public function scopeDraft($query)
    {
        return $query->where('status', 'draft');
    }

    public function scopeSent($query)
    {
        return $query->where('status', 'sent');
    }

    public function scopeReviewed($query)
    {
        return $query->where('status', 'reviewed');
    }
}