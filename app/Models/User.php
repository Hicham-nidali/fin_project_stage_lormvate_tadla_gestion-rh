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
}