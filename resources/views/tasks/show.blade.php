@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1>Détails de la Tâche</h1>
        <a href="{{ route('tasks.index') }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left me-2"></i>Retour
        </a>
    </div>
    
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center bg-light">
            <h5 class="mb-0">{{ $task->title }}</h5>
            <div class="ms-auto">
                @if($task->priority == 'low')
                    <span class="badge bg-success">Basse</span>
                @elseif($task->priority == 'medium')
                    <span class="badge bg-warning">Moyenne</span>
                @else
                    <span class="badge bg-danger">Haute</span>
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
            </div>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-8">
                    <h6 class="mb-3">Description de la tâche</h6>
                    <p>{{ $task->description }}</p>
                    
                    <hr>
                    
                    <h6 class="mb-3">Informations sur l'employé assigné</h6>
                    <div class="d-flex align-items-center mb-3">
                        <div class="avatar-placeholder bg-success text-white rounded-circle d-flex align-items-center justify-content-center" style="width: 50px; height: 50px; font-size: 1.2rem;">
                            {{ substr($task->assignedTo->name, 0, 1) }}
                        </div>
                        <div class="ms-3">
                            <h6 class="mb-0">{{ $task->assignedTo->name }}</h6>
                            <p class="text-muted small mb-0">{{ $task->assignedTo->email }}</p>
                        </div>
                    </div>

                    @if($task->completion_notes)
                    <hr>
                    <h6 class="mb-3">Notes de l'employé</h6>
                    <p class="mb-3">{{ $task->completion_notes }}</p>
                    @endif

                    @if($task->hasCompletionProof())
                    <hr>
                    <h6 class="mb-3">Preuve de completion soumise</h6>
                    <div class="alert alert-info">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <i class="fas fa-file me-2"></i>
                                <strong>{{ $task->completion_proof_name }}</strong>
                                <br>
                                <small class="text-muted">Soumis le {{ $task->completed_at ? $task->completed_at->format('d/m/Y à H:i') : 'N/A' }}</small>
                            </div>
                            <div>
                                @if($task->isImageProof())
                                    <a href="{{ route('tasks.proof.view', $task->id) }}" class="btn btn-sm btn-primary me-2" target="_blank">
                                        <i class="fas fa-eye"></i> Voir
                                    </a>
                                @elseif($task->isPdfProof())
                                    <a href="{{ route('tasks.proof.view', $task->id) }}" class="btn btn-sm btn-primary me-2" target="_blank">
                                        <i class="fas fa-file-pdf"></i> Voir
                                    </a>
                                @else
                                    <span class="btn btn-sm btn-secondary me-2" disabled>
                                        <i class="fas fa-file"></i> Fichier
                                    </span>
                                @endif
                                
                                <a href="{{ route('tasks.proof.download', $task->id) }}" class="btn btn-sm btn-success">
                                    <i class="fas fa-download"></i> Télécharger
                                </a>
                            </div>
                        </div>
                    </div>

                    @if($task->status == 'completed' && $task->hasCompletionProof())
                    <div class="alert alert-warning">
                        <h6><i class="fas fa-clipboard-check me-2"></i>Validation de la tâche</h6>
                        <p class="mb-3">L'employé a marqué cette tâche comme terminée et a fourni une preuve. Veuillez valider ou rejeter le travail.</p>
                        
                        <form action="{{ route('tasks.validate', $task->id) }}" method="POST">
                            @csrf
                            <div class="mb-3">
                                <label class="form-label">Décision</label>
                                <div>
                                    <div class="form-check form-check-inline">
                                        <input class="form-check-input" type="radio" name="validation" id="approve" value="approved" required>
                                        <label class="form-check-label" for="approve">
                                            <i class="fas fa-check text-success"></i> Approuver
                                        </label>
                                    </div>
                                    <div class="form-check form-check-inline">
                                        <input class="form-check-input" type="radio" name="validation" id="reject" value="rejected" required>
                                        <label class="form-check-label" for="reject">
                                            <i class="fas fa-times text-danger"></i> Rejeter
                                        </label>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="mb-3">
                                <label for="feedback" class="form-label">Commentaire (optionnel)</label>
                                <textarea class="form-control" id="feedback" name="feedback" rows="3" placeholder="Ajouter un commentaire..."></textarea>
                            </div>
                            
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-paper-plane me-2"></i>Envoyer la décision
                            </button>
                        </form>
                    </div>
                    @endif
                    @endif
                </div>
                
                <div class="col-md-4">
                    <h6>Détails de la tâche</h6>
                    <ul class="list-group list-group-flush">
                        <li class="list-group-item d-flex justify-content-between px-0">
                            <span>Assigné à:</span>
                            <span>{{ $task->assignedTo->name }}</span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between px-0">
                            <span>Créée par:</span>
                            <span>{{ $task->assignedBy->name }}</span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between px-0">
                            <span>Date de création:</span>
                            <span>{{ $task->created_at->format('d/m/Y') }}</span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between px-0">
                            <span>Date d'échéance:</span>
                            <span>{{ $task->due_date->format('d/m/Y') }}</span>
                        </li>
                        @if($task->completed_at)
                        <li class="list-group-item d-flex justify-content-between px-0">
                            <span>Date de completion:</span>
                            <span>{{ $task->completed_at->format('d/m/Y') }}</span>
                        </li>
                        @endif
                        <li class="list-group-item d-flex justify-content-between px-0">
                            <span>Département:</span>
                            <span>{{ $task->assignedTo->department->name ?? 'N/A' }}</span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between px-0">
                            <span>Temps restant:</span>
                            <span class="{{ $task->due_date < now() && $task->status != 'completed' ? 'text-danger' : 'text-success' }}">
                                @if($task->status == 'completed')
                                    Terminé
                                @elseif($task->due_date < now())
                                    En retard
                                @else
                                    {{ $task->due_date->diffForHumans() }}
                                @endif
                            </span>
                        </li>
                    </ul>
                    
                    <div class="mt-4">
                        <h6>Actions</h6>
                        <div class="d-grid gap-2">
                            <a href="{{ route('tasks.edit', $task->id) }}" class="btn btn-warning">
                                <i class="fas fa-edit me-2"></i>Modifier
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection