@extends('employee.layouts.app')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1>Détails de la Tâche</h1>
        <a href="{{ route('employee.tasks.index') }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left me-2"></i>Retour
        </a>
    </div>
    
    <div class="row">
        <div class="col-md-8">
            <div class="card mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">{{ $task->title }}</h5>
                    <span class="ms-auto">
                        @if($task->priority == 'low')
                            <span class="badge bg-success">Priorité: Basse</span>
                        @elseif($task->priority == 'medium')
                            <span class="badge bg-warning">Priorité: Moyenne</span>
                        @else
                            <span class="badge bg-danger">Priorité: Haute</span>
                        @endif
                        
                        @if($task->status == 'pending')
                            <span class="badge bg-secondary">En attente</span>
                        @elseif($task->status == 'in_progress')
                            <span class="badge bg-primary">En cours</span>
                        @elseif($task->status == 'completed')
                            <span class="badge bg-success">Terminé</span>
                        @else
                            <span class="badge bg-danger">Annulé</span>
                        @endif
                    </span>
                </div>
                <div class="card-body">
                    <h6 class="mb-3">Description</h6>
                    <p>{{ $task->description }}</p>
                    
                    <hr>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <h6>Détails de la tâche</h6>
                            <ul class="list-group list-group-flush">
                                <li class="list-group-item d-flex justify-content-between px-0">
                                    <span>Assignée par:</span>
                                    <span>{{ $task->assignedBy->name }}</span>
                                </li>
                                <li class="list-group-item d-flex justify-content-between px-0">
                                    <span>Date de création:</span>
                                    <span>{{ $task->created_at->format('d/m/Y') }}</span>
                                </li>
                                <li class="list-group-item d-flex justify-content-between px-0">
                                    <span>Date d'échéance:</span>
                                    <span class="{{ $task->due_date < now() && $task->status != 'completed' ? 'text-danger' : '' }}">
                                        {{ $task->due_date->format('d/m/Y') }}
                                        @if($task->due_date < now() && $task->status != 'completed')
                                            <i class="fas fa-exclamation-circle ms-1"></i>
                                        @endif
                                    </span>
                                </li>
                                @if($task->completed_at)
                                <li class="list-group-item d-flex justify-content-between px-0">
                                    <span>Date de complétion:</span>
                                    <span class="text-success">{{ $task->completed_at->format('d/m/Y') }}</span>
                                </li>
                                @endif
                            </ul>
                        </div>
                        <div class="col-md-6">
                            <h6>Statut</h6>
                            <ul class="list-group list-group-flush">
                                <li class="list-group-item d-flex justify-content-between px-0">
                                    <span>Temps restant:</span>
                                    @if($task->status == 'completed')
                                        <span class="text-success">Terminé</span>
                                    @elseif($task->due_date < now())
                                        <span class="text-danger">En retard</span>
                                    @else
                                        <span>{{ now()->diffInDays($task->due_date) }} jours</span>
                                    @endif
                                </li>
                            </ul>
                        </div>
                    </div>

                    @if($task->completion_notes)
                    <hr>
                    <h6>Notes de completion</h6>
                    <p class="text-muted">{{ $task->completion_notes }}</p>
                    @endif

                    @if($task->hasCompletionProof())
                    <hr>
                    <h6>Preuve de completion</h6>
                    <div class="d-flex align-items-center">
                        <i class="fas fa-file me-2 text-success"></i>
                        <a href="{{ $task->completion_proof_url }}" target="_blank" class="btn btn-sm btn-outline-success">
                            <i class="fas fa-download me-2"></i>{{ $task->completion_proof_name }}
                        </a>
                        <span class="badge bg-info ms-2">{{ strtoupper($task->completion_proof_type) }}</span>
                    </div>
                    @endif
                </div>

                @if($task->status != 'completed' && $task->status != 'cancelled')
                <div class="card-footer bg-white">
                    <h6 class="mb-3">Changer le statut de la tâche</h6>
                    
                    <form action="{{ route('employee.tasks.status', $task->id) }}" method="POST" enctype="multipart/form-data" id="statusForm">
                        @csrf
                        
                        <div class="mb-3">
                            <label for="status" class="form-label">Nouveau statut</label>
                            <select class="form-select {{ $errors->has('status') ? 'is-invalid' : '' }}" id="status" name="status" onchange="toggleCompletionFields()">
                                <option value="">Sélectionner un statut</option>
                                @if($task->status == 'pending')
                                    <option value="in_progress">Marquer en cours</option>
                                @endif
                                <option value="completed">Marquer comme terminé</option>
                            </select>
                            @if($errors->has('status'))
                                <div class="invalid-feedback">{{ $errors->first('status') }}</div>
                            @endif
                        </div>

                        <div id="completionFields" style="display: none;">
                            <div class="alert alert-info">
                                <i class="fas fa-info-circle me-2"></i>
                                <strong>Important :</strong> Pour marquer la tâche comme terminée, vous devez fournir une preuve (image, PDF ou document Word).
                            </div>
                            
                            <div class="mb-3">
                                <label for="completion_proof" class="form-label">Preuve de completion <span class="text-danger">*</span></label>
                                <input type="file" class="form-control {{ $errors->has('completion_proof') ? 'is-invalid' : '' }}" 
                                       id="completion_proof" name="completion_proof" 
                                       accept=".jpg,.jpeg,.png,.pdf,.doc,.docx">
                                <div class="form-text">
                                    Formats acceptés : JPG, PNG, PDF, Word. Taille max : 10 MB
                                </div>
                                @if($errors->has('completion_proof'))
                                    <div class="invalid-feedback">{{ $errors->first('completion_proof') }}</div>
                                @endif
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="completion_notes" class="form-label">Notes (optionnel)</label>
                            <textarea class="form-control {{ $errors->has('completion_notes') ? 'is-invalid' : '' }}" 
                                      id="completion_notes" name="completion_notes" rows="3" 
                                      placeholder="Ajoutez des commentaires sur votre travail...">{{ old('completion_notes') }}</textarea>
                            @if($errors->has('completion_notes'))
                                <div class="invalid-feedback">{{ $errors->first('completion_notes') }}</div>
                            @endif
                        </div>

                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save me-2"></i>Mettre à jour
                        </button>
                    </form>
                </div>
                @elseif($task->status == 'completed')
                    <div class="card-footer bg-success text-white text-center">
                        <i class="fas fa-check-circle me-2"></i>
                        Cette tâche a été marquée comme terminée le {{ $task->completed_at->format('d/m/Y à H:i') }}
                    </div>
                @else
                    <div class="card-footer bg-danger text-white text-center">
                        <i class="fas fa-times-circle me-2"></i>
                        Cette tâche a été annulée
                    </div>
                @endif
            </div>
        </div>

        <div class="col-md-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Chef de département</h5>
                </div>
                <div class="card-body">
                    <div class="text-center mb-3">
                        <div class="avatar-placeholder bg-primary text-white rounded-circle d-flex align-items-center justify-content-center mx-auto" style="width: 80px; height: 80px; font-size: 2rem;">
                            {{ substr($task->assignedBy->name, 0, 1) }}
                        </div>
                        <h5 class="mt-3">{{ $task->assignedBy->name }}</h5>
                        <p class="text-muted">Chef de Département</p>
                    </div>
                    
                    <hr>
                    
                    <h6>Instructions</h6>
                    <ul class="list-unstyled">
                        <li><i class="fas fa-check text-success me-2"></i>Lisez attentivement la description</li>
                        <li><i class="fas fa-check text-success me-2"></i>Respectez la date d'échéance</li>
                        <li><i class="fas fa-check text-success me-2"></i>Fournissez une preuve à la fin</li>
                        <li><i class="fas fa-check text-success me-2"></i>Ajoutez des notes si nécessaire</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
function toggleCompletionFields() {
    const status = document.getElementById('status');
    const completionFields = document.getElementById('completionFields');
    const proofInput = document.getElementById('completion_proof');
    
    if (!status || !completionFields || !proofInput) return;
    
    if (status.value === 'completed') {
        completionFields.style.display = 'block';
        proofInput.required = true;
    } else {
        completionFields.style.display = 'none';
        proofInput.required = false;
    }
}

// Validation côté client
document.addEventListener('DOMContentLoaded', function() {
    const statusForm = document.getElementById('statusForm');
    
    if (statusForm) {
        statusForm.addEventListener('submit', function(e) {
            const status = document.getElementById('status');
            const proofInput = document.getElementById('completion_proof');
            
            if (!status || !proofInput) return;
            
            if (status.value === 'completed' && !proofInput.files.length) {
                e.preventDefault();
                alert('Veuillez sélectionner un fichier de preuve pour terminer la tâche.');
                return false;
            }
            
            if (proofInput.files.length > 0) {
                const file = proofInput.files[0];
                const maxSize = 10 * 1024 * 1024; // 10 MB
                
                if (file.size > maxSize) {
                    e.preventDefault();
                    alert('Le fichier est trop volumineux. Taille maximum : 10 MB.');
                    return false;
                }
            }
        });
    }
});
</script>
@endsection