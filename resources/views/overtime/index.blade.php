@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1>Gestion des Heures Supplémentaires</h1>
        <a href="{{ route('overtime.report') }}" class="btn btn-info">
            <i class="fas fa-chart-line me-2"></i>Rapport mensuel
        </a>
    </div>
    
    <div class="card">
        <div class="card-header bg-light">
            <div class="row">
                <div class="col-md-6">
                    <form action="{{ route('overtime.index') }}" method="GET" class="d-flex">
                        <select name="status" class="form-select me-2" style="width: auto;" onchange="this.form.submit()">
                            <option value="">Tous les statuts</option>
                            <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>En attente</option>
                            <option value="approved" {{ request('status') == 'approved' ? 'selected' : '' }}>Approuvé</option>
                            <option value="rejected" {{ request('status') == 'rejected' ? 'selected' : '' }}>Rejeté</option>
                        </select>
                    </form>
                </div>
            </div>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Employé</th>
                            <th>Date</th>
                            <th>Horaires</th>
                            <th>Heures demandées</th>
                            <th>Type</th>
                            <th>Statut</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($overtimeRecords as $record)
                        <tr>
                            <td>{{ $record->user->name }}</td>
                            <td>{{ $record->overtime_date->format('d/m/Y') }}</td>
                            <td>
                                {{ \Carbon\Carbon::parse($record->start_time)->format('H:i') }} - 
                                {{ \Carbon\Carbon::parse($record->end_time)->format('H:i') }}
                            </td>
                            <td>{{ $record->hours_requested }}h</td>
                            <td>
                                @php
                                    $metadata = json_decode($record->request->description, true);
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
                            </td>
                            <td>
                                @if($record->status == 'pending')
                                    <span class="badge bg-warning">En attente</span>
                                @elseif($record->status == 'approved')
                                    <span class="badge bg-success">Approuvé</span>
                                @else
                                    <span class="badge bg-danger">Rejeté</span>
                                @endif
                            </td>
                            <td>
                                <a href="{{ route('overtime.show', $record->id) }}" class="btn btn-sm btn-info">
                                    <i class="fas fa-eye"></i>
                                </a>
                                @if($record->status == 'pending')
                                    <form method="POST" action="{{ route('overtime.approve', $record->id) }}" class="d-inline">
                                        @csrf
                                        <button type="submit" class="btn btn-sm btn-success" onclick="return confirm('Approuver ces heures supplémentaires ?')">
                                            <i class="fas fa-check"></i>
                                        </button>
                                    </form>
                                    <form method="POST" action="{{ route('overtime.reject', $record->id) }}" class="d-inline">
                                        @csrf
                                        <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Rejeter ces heures supplémentaires ?')">
                                            <i class="fas fa-times"></i>
                                        </button>
                                    </form>
                                @endif
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            
            @if($overtimeRecords->isEmpty())
                <div class="alert alert-info">
                    <i class="fas fa-info-circle me-2"></i>Aucune demande d'heures supplémentaires trouvée.
                </div>
            @endif
        </div>
    </div>
</div>
@endsection