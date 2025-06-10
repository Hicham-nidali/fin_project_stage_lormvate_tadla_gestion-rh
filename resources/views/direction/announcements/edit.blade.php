@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1>Modifier l'Annonce</h1>
        <a href="{{ route('direction.announcements.index') }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left me-2"></i>Retour
        </a>
    </div>

    <div class="row">
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Modifier l'Annonce</h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('direction.announcements.update', $announcement->id) }}" method="POST">
                        @csrf
                        @method('PUT')
                        
                        <div class="mb-3">
                            <label for="title" class="form-label">Titre de l'annonce *</label>
                            <input type="text" class="form-control @error('title') is-invalid @enderror" 
                                   id="title" name="title" value="{{ old('title', $announcement->title) }}" required>
                            @error('title')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="content" class="form-label">Contenu de l'annonce *</label>
                            <textarea class="form-control @error('content') is-invalid @enderror" 
                                      id="content" name="content" rows="6" required>{{ old('content', $announcement->content) }}</textarea>
                            @error('content')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="priority" class="form-label">Priorité *</label>
                                    <select class="form-select @error('priority') is-invalid @enderror" 
                                            id="priority" name="priority" required>
                                        <option value="normal" {{ old('priority', $announcement->priority) === 'normal' ? 'selected' : '' }}>Normal</option>
                                        <option value="high" {{ old('priority', $announcement->priority) === 'high' ? 'selected' : '' }}>Priorité Élevée</option>
                                        <option value="urgent" {{ old('priority', $announcement->priority) === 'urgent' ? 'selected' : '' }}>Urgent</option>
                                    </select>
                                    @error('priority')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="status" class="form-label">Statut *</label>
                                    <select class="form-select @error('status') is-invalid @enderror" 
                                            id="status" name="status" required>
                                        <option value="draft" {{ old('status', $announcement->status) === 'draft' ? 'selected' : '' }}>Brouillon</option>
                                        <option value="published" {{ old('status', $announcement->status) === 'published' ? 'selected' : '' }}>Publié</option>
                                    </select>
                                    @error('status')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <hr>

                        <h6 class="mb-3">Informations de Réunion (optionnel)</h6>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="meeting_date" class="form-label">Date et heure de la réunion</label>
                                    <input type="datetime-local" 
                                           class="form-control @error('meeting_date') is-invalid @enderror" 
                                           id="meeting_date" name="meeting_date" 
                                           value="{{ old('meeting_date', $announcement->meeting_date ? $announcement->meeting_date->format('Y-m-d\TH:i') : '') }}"
                                           min="{{ now()->format('Y-m-d\TH:i') }}">
                                    @error('meeting_date')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="meeting_location" class="form-label">Lieu de la réunion</label>
                                    <input type="text" class="form-control @error('meeting_location') is-invalid @enderror" 
                                           id="meeting_location" name="meeting_location" 
                                           value="{{ old('meeting_location', $announcement->meeting_location) }}"
                                           placeholder="Ex: Salle de conférence, Visioconférence...">
                                    @error('meeting_location')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="d-flex justify-content-end gap-2">
                            <a href="{{ route('direction.announcements.index') }}" class="btn btn-secondary">
                                <i class="fas fa-times me-2"></i>Annuler
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save me-2"></i>Mettre à jour
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        
        <div class="col-lg-4">
            <!-- Informations actuelles -->
            <div class="card">
                <div class="card-header">
                    <h6 class="mb-0"><i class="fas fa-info-circle me-2"></i>Informations</h6>
                </div>
                <div class="card-body">
                    <ul class="list-unstyled">
                        <li class="mb-2">
                            <strong>Créé le :</strong><br>
                            {{ $announcement->created_at->format('d/m/Y H:i') }}
                        </li>
                        <li class="mb-2">
                            <strong>Statut actuel :</strong><br>
                            <span class="badge bg-{{ $announcement->status === 'published' ? 'success' : 'warning' }}">
                                {{ $announcement->status_label }}
                            </span>
                        </li>
                        <li class="mb-2">
                            <strong>Priorité actuelle :</strong><br>
                            <span class="badge bg-{{ $announcement->priority_color }}">
                                {{ $announcement->priority_label }}
                            </span>
                        </li>
                        @if($announcement->status === 'published')
                        <li class="mb-2">
                            <strong>Statistiques de lecture :</strong><br>
                            {{ $announcement->getReadPercentage() }}% 
                            ({{ $announcement->getReadCount() }}/{{ $announcement->getTotalPotentialReaders() }})
                        </li>
                        @endif
                    </ul>
                </div>
            </div>

            @if($announcement->status === 'published')
            <!-- Avertissement modification -->
            <div class="card mt-3 border-warning">
                <div class="card-header bg-warning text-dark">
                    <h6 class="mb-0"><i class="fas fa-exclamation-triangle me-2"></i>Attention</h6>
                </div>
                <div class="card-body">
                    <p class="small">Cette annonce est déjà publiée. Les modifications seront immédiatement visibles par tous les utilisateurs.</p>
                </div>
            </div>
            @endif
        </div>
    </div>
</div>
@endsection