@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1>Détails des Heures Supplémentaires</h1>
        <a href="{{ route('overtime.index') }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left me-2"></i>Retour
        </a>
    </div>
    
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center bg-light">
            <h5 class="mb-0">{{ $overtimeRecord->request->title }}</h5>
            <span class="ms-auto">
                @php
                    $metadata = json_decode($overtimeRecord->request->description, true);
                    $overtimeType = is_array($metadata) ? ($metadata['overtime_type'] ?? 'Non spécifié') : 'Non spécifié';
                @endphp
                @if($overtimeType == 'planned')
                    <span class="badge bg-info">Planifiées</span>
                @elseif($overtimeType == 'urgent')
                    <span class="badge bg-warning">Urgentes</span>
                @elseif($overtimeType == 'project')
                    <span class="badge bg-primary">Projet</span>
                @else
                    <span class="badge bg-secondary">{{ $overtimeType }}</span>
                @endif
                
                @if($overtimeRecord->status == 'pending')
                    <span class="badge bg-warning">En attente</span>
                @elseif($overtimeRecord->status == 'approved')
                    <span class="badge bg-success">Approuvé</span>
                @else
                    <span class="badge bg-danger">Rejeté</span>
                @endif
            </span>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <h6 class="mb-3">Informations de l'employé</h6>
                    <ul class="list-group list-group-flush">
                        <li class="list-group-item d-flex justify-content-between px-0">
                            <span>Nom:</span>
                            <span class="text-primary">{{ $overtimeRecord->user->name }}</span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between px-0">
                            <span>Département:</span>
                            <span>{{ $overtimeRecord->department->name }}</span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between px-0">
                            <span>Email:</span>
                            <span>{{ $overtimeRecord->user->email }}</span>
                        </li>
                    </ul>
                </div>
                <div class="col-md-6">
                    <h6 class="mb-3">Détails des heures supplémentaires</h6>
                    <ul class="list-group list-group-flush">
                        <li class="list-group-item d-flex justify-content-between px-0">
                            <span>Date:</span>
                            <span>{{ $overtimeRecord->overtime_date->format('d/m/Y') }}</span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between px-0">
                            <span>Heure de début:</span>
                            <span>{{ \Carbon\Carbon::parse($overtimeRecord->start_time)->format('H:i') }}</span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between px-0">
                            <span>Heure de fin:</span>
                            <span>{{ \Carbon\Carbon::parse($overtimeRecord->end_time)->format('H:i') }}</span>
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
                        <li class="list-group-item d-flex justify-content-between px-0">
                            <span>Taux:</span>
                            @php
                                $rate = is_array($metadata) ? ($metadata['overtime_rate'] ?? 1.25) : 1.25;
                            @endphp
                            <span>{{ ($rate * 100) }}%</span>
                        </li>
                    </ul>
                </div>
            </div>
            
            <hr>
            
            <h6 class="mb-3">Raison des heures supplémentaires</h6>
            <p class="border p-3 bg-light rounded">{{ $overtimeRecord->reason }}</p>
            
            @if($overtimeRecord->approved_at)
            <hr>
            <div class="row">
                <div class="col-md-6">
                    <h6>Approbation</h6>
                    <ul class="list-group list-group-flush">
                        <li class="list-group-item d-flex justify-content-between px-0">
                            <span>Approuvé par:</span>
                            <span>{{ $overtimeRecord->approver->name }}</span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between px-0">
                            <span>Date d'approbation:</span>
                            <span>{{ $overtimeRecord->approved_at->format('d/m/Y H:i') }}</span>
                        </li>
                    </ul>
                </div>
            </div>
            @endif
        </div>
        
        @if($overtimeRecord->status == 'pending')
        <div class="card-footer bg-white">
            <div class="d-flex justify-content-around">
                <form method="POST" action="{{ route('overtime.approve', $overtimeRecord->id) }}" class="d-inline">
                    @csrf
                    <button type="submit" class="btn btn-success" onclick="return confirm('Approuver ces heures supplémentaires ?')">
                        <i class="fas fa-check me-2"></i>Approuver
                    </button>
                </form>
                <form method="POST" action="{{ route('overtime.reject', $overtimeRecord->id) }}" class="d-inline">
                    @csrf
                    <button type="submit" class="btn btn-danger" onclick="return confirm('Rejeter ces heures supplémentaires ?')">
                        <i class="fas fa-times me-2"></i>Rejeter
                    </button>
                </form>
            </div>
        </div>
        @elseif($overtimeRecord->status == 'approved')
            <div class="card-footer bg-light">
                <div class="text-center text-success">
                    <i class="fas fa-check-circle me-1"></i> Ces heures supplémentaires ont été approuvées le {{ $overtimeRecord->approved_at->format('d/m/Y') }}
                </div>
            </div>
        @else
            <div class="card-footer bg-light">
                <div class="text-center text-danger">
                    <i class="fas fa-times-circle me-1"></i> Ces heures supplémentaires ont été rejetées le {{ $overtimeRecord->approved_at->format('d/m/Y') }}
                </div>
            </div>
        @endif
    </div>
</div>
@endsection