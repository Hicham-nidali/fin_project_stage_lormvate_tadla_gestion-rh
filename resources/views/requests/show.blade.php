<!-- resources/views/requests/show.blade.php -->
@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1>Détails de la Demande</h1>
        <a href="{{ route('requests.index') }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left me-2"></i>Retour
        </a>
    </div>
    
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center bg-light">
            <h5 class="mb-0">{{ $request->title }}</h5>
            <span class="ms-auto">
                @if($request->type == 'leave')
                    <span class="badge bg-info">Congé</span>
                @elseif($request->type == 'expense')
                    <span class="badge bg-warning">Remboursement</span>
                @elseif($request->type == 'equipment')
                    <span class="badge bg-primary">Équipement</span>
                @else
                    <span class="badge bg-secondary">Autre</span>
                @endif
                
                @if($request->status == 'pending')
                    <span class="badge bg-warning">En attente</span>
                @elseif($request->status == 'approved')
                    <span class="badge bg-success">Approuvé</span>
                @else
                    <span class="badge bg-danger">Rejeté</span>
                @endif
            </span>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-8">
                    <h6 class="mb-3">Description de la demande</h6>
                    <p>{{ $request->description }}</p>
                    
                    <hr>
                    
                    <h6 class="mb-3">Informations sur le demandeur</h6>
                    <div class="d-flex align-items-center mb-3">
                        <div class="avatar-placeholder bg-primary text-white rounded-circle d-flex align-items-center justify-content-center" style="width: 50px; height: 50px; font-size: 1.2rem;">
                            {{ substr($request->user->name, 0, 1) }}
                        </div>
                        <div class="ms-3">
                            <h6 class="mb-0">{{ $request->user->name }}</h6>
                            <p class="text-muted small mb-0">{{ $request->user->email }}</p>
                            <p class="text-muted small mb-0">Département: {{ $request->department->name }}</p>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-4">
                    <h6>Détails de la demande</h6>
                    <ul class="list-group list-group-flush">
                        <li class="list-group-item d-flex justify-content-between px-0">
                            <span>Date de soumission:</span>
                            <span>{{ $request->created_at->format('d/m/Y H:i') }}</span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between px-0">
                            <span>Type:</span>
                            <span>
                                @if($request->type == 'leave')
                                    Congé
                                @elseif($request->type == 'expense')
                                    Remboursement
                                @elseif($request->type == 'equipment')
                                    Équipement
                                @else
                                    Autre
                                @endif
                            </span>
                        </li>
                        @if($request->approved_at)
                        <li class="list-group-item d-flex justify-content-between px-0">
                            <span>Date de réponse:</span>
                            <span>{{ $request->approved_at->format('d/m/Y H:i') }}</span>
                        </li>
                        @endif
                        @if($request->approver)
                        <li class="list-group-item d-flex justify-content-between px-0">
                            <span>Traité par:</span>
                            <span>{{ $request->approver->name }}</span>
                        </li>
                        @endif
                    </ul>
                    
                    @if($request->status == 'pending')
                    <div class="mt-4">
                        <h6>Actions</h6>
                        <div class="d-grid gap-2">
                            <form action="{{ route('requests.approve', $request->id) }}" method="POST" class="d-inline">
                                @csrf
                                <button type="submit" class="btn btn-success w-100" onclick="return confirm('Êtes-vous sûr de vouloir approuver cette demande ?')">
                                    <i class="fas fa-check me-2"></i>Approuver
                                </button>
                            </form>
                            
                            <form action="{{ route('requests.reject', $request->id) }}" method="POST" class="d-inline">
                                @csrf
                                <button type="submit" class="btn btn-danger w-100" onclick="return confirm('Êtes-vous sûr de vouloir rejeter cette demande ?')">
                                    <i class="fas fa-times me-2"></i>Rejeter
                                </button>
                            </form>
                        </div>
                    </div>
                    @endif
                </div>
            </div>
            
            <hr class="my-4">
            
            <div class="row">
                <div class="col-md-12">
                    <h6 class="mb-3">Fichiers joints</h6>
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle me-2"></i>Aucun fichier joint à cette demande.
                    </div>
                    
                    <!-- Si vous voulez afficher des fichiers joints, vous pouvez utiliser cette structure :
                    <div class="list-group">
                        <div class="list-group-item">
                            <div class="d-flex align-items-center">
                                <i class="fas fa-file-pdf text-danger me-2"></i>
                                <div>
                                    <div>Document.pdf</div>
                                    <small class="text-muted">Taille: 1.2 MB</small>
                                </div>
                                <a href="#" class="btn btn-sm btn-outline-primary ms-auto">
                                    <i class="fas fa-download"></i> Télécharger
                                </a>
                            </div>
                        </div>
                    </div>
                    -->
                </div>
            </div>
        </div>
        
        <div class="card-footer bg-white">
            @if($request->status == 'pending')
                <div class="alert alert-warning mb-0">
                    <i class="fas fa-clock me-2"></i>Cette demande est en attente de votre décision.
                </div>
            @elseif($request->status == 'approved')
                <div class="alert alert-success mb-0">
                    <i class="fas fa-check-circle me-2"></i>Cette demande a été approuvée le {{ $request->approved_at->format('d/m/Y à H:i') }} par {{ $request->approver->name }}.
                </div>
            @else
                <div class="alert alert-danger mb-0">
                    <i class="fas fa-times-circle me-2"></i>Cette demande a été rejetée le {{ $request->approved_at->format('d/m/Y à H:i') }} par {{ $request->approver->name }}.
                </div>
            @endif
        </div>
    </div>
    
    <!-- Historique des actions (optionnel) -->
    <div class="card mt-4">
        <div class="card-header bg-light">
            <h5 class="mb-0">Historique des actions</h5>
        </div>
        <div class="card-body">
            <div class="timeline">
                <div class="timeline-item">
                    <div class="timeline-marker bg-primary"></div>
                    <div class="timeline-content">
                        <h6 class="timeline-title">Demande soumise</h6>
                        <p class="timeline-text">{{ $request->user->name }} a soumis cette demande</p>
                        <small class="text-muted">{{ $request->created_at->format('d/m/Y à H:i') }}</small>
                    </div>
                </div>
                
                @if($request->approved_at)
                <div class="timeline-item">
                    <div class="timeline-marker {{ $request->status == 'approved' ? 'bg-success' : 'bg-danger' }}"></div>
                    <div class="timeline-content">
                        <h6 class="timeline-title">Demande {{ $request->status == 'approved' ? 'approuvée' : 'rejetée' }}</h6>
                        <p class="timeline-text">{{ $request->approver->name }} a {{ $request->status == 'approved' ? 'approuvé' : 'rejeté' }} cette demande</p>
                        <small class="text-muted">{{ $request->approved_at->format('d/m/Y à H:i') }}</small>
                    </div>
                </div>
                @endif
            </div>
        </div>
    </div>
</div>

<style>
.timeline {
    position: relative;
    padding-left: 2rem;
}

.timeline-item {
    position: relative;
    margin-bottom: 1.5rem;
}

.timeline-marker {
    position: absolute;
    left: -2rem;
    top: 0.25rem;
    width: 1rem;
    height: 1rem;
    border-radius: 50%;
    border: 2px solid #fff;
}

.timeline::before {
    content: '';
    position: absolute;
    left: -1.5rem;
    top: 0;
    bottom: 0;
    width: 2px;
    background-color: #dee2e6;
}

.timeline-title {
    margin-bottom: 0.25rem;
    font-weight: 600;
}

.timeline-text {
    margin-bottom: 0.25rem;
    color: #6c757d;
}

.avatar-placeholder {
    flex-shrink: 0;
}
</style>
@endsection