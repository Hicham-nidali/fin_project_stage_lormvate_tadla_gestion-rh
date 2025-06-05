<?php
// app/Models/Task.php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class Task extends Model
{
    use HasFactory;

    protected $fillable = [
        'title', 'description', 'status', 'priority', 
        'assigned_to', 'assigned_by', 'department_id',
        'due_date', 'completed_at', 'completion_proof', 'completion_notes'
    ];

    protected $casts = [
        'due_date' => 'datetime',
        'completed_at' => 'datetime',
    ];

    public function assignedTo()
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function assignedBy()
    {
        return $this->belongsTo(User::class, 'assigned_by');
    }

    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    /**
     * Obtenir l'URL du fichier de preuve - CORRIGÉ
     */
    public function getCompletionProofUrlAttribute()
    {
        if ($this->completion_proof) {
            // Utiliser route personnalisée au lieu de Storage::url
            return route('tasks.proof.download', ['task' => $this->id]);
        }
        return null;
    }

    /**
     * Obtenir l'URL de visualisation du fichier
     */
    public function getCompletionProofViewUrlAttribute()
    {
        if ($this->completion_proof) {
            return route('tasks.proof.view', ['task' => $this->id]);
        }
        return null;
    }

    /**
     * Obtenir le chemin complet du fichier
     */
    public function getCompletionProofFullPathAttribute()
    {
        if ($this->completion_proof) {
            return storage_path('app/public/' . $this->completion_proof);
        }
        return null;
    }

    /**
     * Obtenir le nom du fichier de preuve
     */
    public function getCompletionProofNameAttribute()
    {
        if ($this->completion_proof) {
            return basename($this->completion_proof);
        }
        return null;
    }

    /**
     * Obtenir le type de fichier de preuve
     */
    public function getCompletionProofTypeAttribute()
    {
        if ($this->completion_proof) {
            $extension = pathinfo($this->completion_proof, PATHINFO_EXTENSION);
            return strtolower($extension);
        }
        return null;
    }

    /**
     * Obtenir le type MIME du fichier
     */
    public function getCompletionProofMimeTypeAttribute()
    {
        if ($this->completion_proof && Storage::disk('public')->exists($this->completion_proof)) {
            return Storage::disk('public')->mimeType($this->completion_proof);
        }
        return null;
    }

    /**
     * Vérifier si la tâche a une preuve
     */
    public function hasCompletionProof()
    {
        return $this->completion_proof && Storage::disk('public')->exists($this->completion_proof);
    }

    /**
     * Vérifier si le fichier est une image
     */
    public function isImageProof()
    {
        $imageTypes = ['jpg', 'jpeg', 'png', 'gif', 'bmp', 'webp'];
        return in_array($this->completion_proof_type, $imageTypes);
    }

    /**
     * Vérifier si le fichier est un PDF
     */
    public function isPdfProof()
    {
        return $this->completion_proof_type === 'pdf';
    }

    /**
     * Supprimer le fichier de preuve
     */
    public function deleteCompletionProof()
    {
        if ($this->completion_proof && Storage::disk('public')->exists($this->completion_proof)) {
            Storage::disk('public')->delete($this->completion_proof);
        }
    }

    /**
     * Boot method pour gérer la suppression des fichiers
     */
    protected static function boot()
    {
        parent::boot();

        static::deleting(function ($task) {
            $task->deleteCompletionProof();
        });
    }
}