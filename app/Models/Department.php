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

    // 🆕 Relation avec les rapports d'évaluation
    public function evaluationReports()
    {
        return $this->hasMany(EvaluationReport::class);
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
}