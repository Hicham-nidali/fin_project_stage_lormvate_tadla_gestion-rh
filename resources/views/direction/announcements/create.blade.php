@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1>Nouvelle Annonce</h1>
        <a href="{{ route('direction.announcements.index') }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left me-2"></i>Retour
        </a>
    </div>

    <div class="row">
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Informations de l'Annonce</h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('direction.announcements.store') }}" method="POST">
                        @csrf
                        
                        <div class="mb-3">
                            <label for="title" class="form-label">Titre de l'annonce *</label>
                            <input type="text" class="form-control @error('title') is-invalid @enderror" 
                                   id="title" name="title" value="{{ old('title') }}" required>
                            @error('title')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="content" class="form-label">Contenu de l'annonce *</label>
                            <textarea class="form-control @error('content') is-invalid @enderror" 
                                      id="content" name="content" rows="6" required>{{ old('content') }}</textarea>
                            @error('content')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <div class="form-text">Décrivez le contenu de votre annonce en détail.</div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="priority" class="form-label">Priorité *</label>
                                    <select class="form-select @error('priority') is-invalid @enderror" 
                                            id="priority" name="priority" required>
                                        <option value="">Sélectionner une priorité</option>
                                        <option value="normal" {{ old('priority') === 'normal' ? 'selected' : '' }}>Normal</option>
                                        <option value="high" {{ old('priority') === 'high' ? 'selected' : '' }}>Priorité Élevée</option>
                                        <option value="urgent" {{ old('priority') === 'urgent' ? 'selected' : '' }}>Urgent</option>
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
                                        <option value="draft" {{ old('status', 'draft') === 'draft' ? 'selected' : '' }}>Sauvegarder en brouillon</option>
                                        <option value="published" {{ old('status') === 'published' ? 'selected' : '' }}>Publier immédiatement</option>
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
                                           value="{{ old('meeting_date') }}"
                                           min="{{ now()->format('Y-m-d\TH:i') }}">
                                    @error('meeting_date')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <div class="form-text">Laissez vide si cette annonce ne concerne pas une réunion.</div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="meeting_location" class="form-label">Lieu de la réunion</label>
                                    <input type="text" class="form-control @error('meeting_location') is-invalid @enderror" 
                                           id="meeting_location" name="meeting_location" 
                                           value="{{ old('meeting_location') }}"
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
                            <button type="submit" class="btn btn-primary" id="submitBtn">
                                <i class="fas fa-save me-2"></i><span id="submitText">Créer l'annonce</span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        
        <div class="col-lg-4">
            <!-- Aide -->
            <div class="card">
                <div class="card-header">
                    <h6 class="mb-0"><i class="fas fa-lightbulb me-2"></i>Conseils</h6>
                </div>
                <div class="card-body">
                    <ul class="list-unstyled">
                        <li class="mb-2">
                            <strong>Titre clair :</strong> Utilisez un titre explicite et accrocheur.
                        </li>
                        <li class="mb-2">
                            <strong>Contenu structuré :</strong> Organisez votre message avec des paragraphes clairs.
                        </li>
                        <li class="mb-2">
                            <strong>Priorité :</strong> 
                            <br>• Normal : Informations générales
                            <br>• Élevée : Informations importantes
                            <br>• Urgent : Nécessite une attention immédiate
                        </li>
                        <li class="mb-2">
                            <strong>Réunion :</strong> Si votre annonce concerne une réunion, précisez la date, l'heure et le lieu.
                        </li>
                    </ul>
                </div>
            </div>

            <!-- Aperçu priorité -->
            <div class="card mt-3">
                <div class="card-header">
                    <h6 class="mb-0"><i class="fas fa-eye me-2"></i>Aperçu Priorité</h6>
                </div>
                <div class="card-body" id="priorityPreview">
                    <span class="badge bg-primary">Normal</span>
                    <p class="mt-2 small text-muted">L'annonce apparaîtra avec cette priorité pour tous les utilisateurs.</p>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const prioritySelect = document.getElementById('priority');
    const statusSelect = document.getElementById('status');
    const submitBtn = document.getElementById('submitBtn');
    const submitText = document.getElementById('submitText');
    const priorityPreview = document.getElementById('priorityPreview');

    // Mise à jour du texte du bouton en fonction du statut
    function updateSubmitButton() {
        const status = statusSelect.value;
        if (status === 'published') {
            submitText.textContent = 'Créer et Publier';
            submitBtn.className = 'btn btn-success';
        } else {
            submitText.textContent = 'Sauvegarder en Brouillon';
            submitBtn.className = 'btn btn-primary';
        }
    }

    // Mise à jour de l'aperçu de priorité
    function updatePriorityPreview() {
        const priority = prioritySelect.value;
        const colors = {
            'normal': 'primary',
            'high': 'warning', 
            'urgent': 'danger'
        };
        const labels = {
            'normal': 'Normal',
            'high': 'Priorité Élevée',
            'urgent': 'Urgent'
        };

        if (priority) {
            priorityPreview.innerHTML = `
                <span class="badge bg-${colors[priority]}">${labels[priority]}</span>
                <p class="mt-2 small text-muted">L'annonce apparaîtra avec cette priorité pour tous les utilisateurs.</p>
            `;
        }
    }

    statusSelect.addEventListener('change', updateSubmitButton);
    prioritySelect.addEventListener('change', updatePriorityPreview);

    // Initialisation
    updateSubmitButton();
    updatePriorityPreview();
});
</script>
@endsection