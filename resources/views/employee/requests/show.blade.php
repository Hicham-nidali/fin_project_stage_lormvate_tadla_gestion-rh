@extends('employee.layouts.app')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1>Détails de la Demande</h1>
        <a href="{{ route('employee.requests.index') }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left me-2"></i>Retour
        </a>
    </div>
    
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center bg-light">
            <h5 class="mb-0">{{ $employeeRequest->title }}</h5>
            <span class="ms-auto">
                @if($employeeRequest->type == 'leave')
                    <span class="badge bg-info">Congé</span>
                @elseif($employeeRequest->type == 'expense')
                    <span class="badge bg-warning">Remboursement</span>
                @elseif($employeeRequest->type == 'equipment')
                    <span class="badge bg-primary">Équipement</span>
                @elseif($employeeRequest->type == 'overtime')
                    <span class="badge bg-purple">Heures supplémentaires</span>
                @else
                    <span class="badge bg-secondary">Autre</span>
                @endif
                
                @if($employeeRequest->status == 'pending')
                    <span class="badge bg-warning">En attente</span>
                @elseif($employeeRequest->status == 'approved')
                    <span class="badge bg-success">Approuvé</span>
                @else
                    <span class="badge bg-danger">Rejeté</span>
                @endif
            </span>
        </div>
        <div class="card-body">
            @if($employeeRequest->type == 'overtime' && $overtimeRecord)
                <!-- Affichage spécial pour les heures supplémentaires -->
                <h6 class="mb-3">Détails des heures supplémentaires</h6>
                <div class="row mb-4">
                    <div class="col-md-6">
                        <ul class="list-group list-group-flush">
                            <li class="list-group-item d-flex justify-content-between px-0">
                                <span>Date:</span>
                                <span>{{ $overtimeRecord->overtime_date->format('d/m/Y') }}</span>
                            </li>
                            <li class="list-group-item d-flex justify-content-between px-0">
                                <span>Horaires:</span>
                                <span>{{ \Carbon\Carbon::parse($overtimeRecord->start_time)->format('H:i') }} - {{ \Carbon\Carbon::parse($overtimeRecord->end_time)->format('H:i') }}</span>
                            </li>
                            <li class="list-group-item d-flex justify-content-between px-0">
                                <span>Heures demandées:</span>
                                <span class="fw-bold">{{ $overtimeRecord->hours_requested }}h</span>
                            </li>
                            @if($overtimeRecord->hours_approved)
                            <li class="list-group-item d-flex justify-content-between px-0">
                                <span>Heures approuvées:</span>
                                <span class="fw-bold text-success">{{ $overtimeRecord->hours_approved }}h</span>
                            </li>
                            @endif
                        </ul>
                    </div>
                    <div class="col-md-6">
                        <ul class="list-group list-group-flush">
                            @php
                                $metadata = json_decode($employeeRequest->description, true);
                                $overtimeType = is_array($metadata) ? ($metadata['overtime_type'] ?? 'Non spécifié') : 'Non spécifié';
                                $overtimeRate = is_array($metadata) ? ($metadata['overtime_rate'] ?? 1.25) : 1.25;
                            @endphp
                            <li class="list-group-item d-flex justify-content-between px-0">
                                <span>Type:</span>
                                <span>
                                    @if($overtimeType == 'planned')
                                        <span class="badge bg-info">Planifiées</span>
                                    @elseif($overtimeType == 'urgent')
                                        <span class="badge bg-warning">Urgentes</span>
                                    @elseif($overtimeType == 'project')
                                        <span class="badge bg-primary">Projet</span>
                                    @else
                                        {{ $overtimeType }}
                                    @endif
                                </span>
                            </li>
                            <li class="list-group-item d-flex justify-content-between px-0">
                                <span>Taux de majoration:</span>
                                <span>{{ ($overtimeRate * 100) }}%</span>
                            </li>
                            <li class="list-group-item d-flex justify-content-between px-0">
                                <span>Statut:</span>
                                <span>
                                    @if($overtimeRecord->status == 'pending')
                                        <span class="badge bg-warning">En attente</span>
                                    @elseif($overtimeRecord->status == 'approved')
                                        <span class="badge bg-success">Approuvé</span>
                                    @else
                                        <span class="badge bg-danger">Rejeté</span>
                                    @endif
                                </span>
                            </li>
                        </ul>
                    </div>
                </div>
                
                <h6 class="mb-3">Raison des heures supplémentaires</h6>
                <p class="border p-3 bg-light rounded">{{ $overtimeRecord->reason }}</p>
            @else
                <!-- Affichage normal pour les autres types de demandes -->
                <h6 class="mb-3">Description de la demande</h6>
                <p>{{ $employeeRequest->description }}</p>
            @endif
            
            <hr>
            
            <div class="row">
                <div class="col-md-6">
                    <h6>Détails de la demande</h6>
                    <ul class="list-group list-group-flush">
                        <li class="list-group-item d-flex justify-content-between px-0">
                            <span>Date de soumission:</span>
                            <span>{{ $employeeRequest->created_at->format('d/m/Y H:i') }}</span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between px-0">
                            <span>Département:</span>
                            <span>{{ $employeeRequest->department->name }}</span>
                        </li>
                        @if($employeeRequest->approved_at)
                        <li class="list-group-item d-flex justify-content-between px-0">
                            <span>Date de réponse:</span>
                            <span>{{ $employeeRequest->approved_at->format('d/m/Y H:i') }}</span>
                        </li>
                        @endif
                        @if($employeeRequest->approver)
                        <li class="list-group-item d-flex justify-content-between px-0">
                            <span>Traité par:</span>
                            <span>{{ $employeeRequest->approver->name }}</span>
                        </li>
                        @endif
                    </ul>
                </div>
                <div class="col-md-6">
                    <h6>Actions</h6>
                    <div class="d-grid gap-2">
                        <button type="button" class="btn btn-outline-primary">
                            <i class="fas fa-print me-2"></i>Imprimer
                        </button>
                        @if($employeeRequest->status == 'pending')
                        <button type="button" class="btn btn-outline-secondary">
                            <i class="fas fa-edit me-2"></i>Modifier (bientôt)
                        </button>
                        @endif
                    </div>
                </div>
            </div>
        </div>
        <div class="card-footer bg-white">
            @if($employeeRequest->status == 'pending')
                <div class="alert alert-warning mb-0">
                    <i class="fas fa-clock me-2"></i>Votre demande est en cours d'examen par votre responsable.
                </div>
            @elseif($employeeRequest->status == 'approved')
                <div class="alert alert-success mb-0">
                    <i class="fas fa-check-circle me-2"></i>Votre demande a été approuvée le {{ $employeeRequest->approved_at->format('d/m/Y') }} par {{ $employeeRequest->approver->name }}.
                    @if($employeeRequest->type == 'overtime' && $overtimeRecord && $overtimeRecord->hours_approved)
                        <br><strong>{{ $overtimeRecord->hours_approved }}h d'heures supplémentaires approuvées.</strong>
                    @endif
                </div>
            @else
                <div class="alert alert-danger mb-0">
                    <i class="fas fa-times-circle me-2"></i>Votre demande a été rejetée le {{ $employeeRequest->approved_at->format('d/m/Y') }} par {{ $employeeRequest->approver->name }}.
                </div>
            @endif
        </div>
    </div>
</div>
@endsection