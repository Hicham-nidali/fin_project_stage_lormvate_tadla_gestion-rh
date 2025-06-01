@extends('employee.layouts.app')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1>Mes Demandes</h1>
        <a href="{{ route('employee.requests.create') }}" class="btn btn-primary">
            <i class="fas fa-plus me-2"></i>Nouvelle Demande
        </a>
    </div>
    
    <div class="card mb-4">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Titre</th>
                            <th>Type</th>
                            <th>Date de demande</th>
                            <th>Statut</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($requests as $request)
                        <tr>
                            <td>{{ $request->title }}</td>
                            <td>
                                @if($request->type == 'leave')
                                    <span class="badge bg-info">Congé</span>
                                @elseif($request->type == 'expense')
                                    <span class="badge bg-warning">Remboursement</span>
                                @elseif($request->type == 'equipment')
                                    <span class="badge bg-primary">Équipement</span>
                                @elseif($request->type == 'overtime')
                                    <span class="badge bg-purple">Heures supplémentaires</span>
                                @else
                                    <span class="badge bg-secondary">Autre</span>
                                @endif
                            </td>
                            <td>{{ $request->created_at->format('d/m/Y') }}</td>
                            <td>
                                @if($request->status == 'pending')
                                    <span class="badge bg-warning">En attente</span>
                                @elseif($request->status == 'approved')
                                    <span class="badge bg-success">Approuvé</span>
                                @else
                                    <span class="badge bg-danger">Rejeté</span>
                                @endif
                            </td>
                            <td>
                                <a href="{{ route('employee.requests.show', $request->id) }}" class="btn btn-sm btn-info">
                                    <i class="fas fa-eye"></i>
                                </a>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            
            @if($requests->isEmpty())
                <div class="alert alert-info">
                    <i class="fas fa-info-circle me-2"></i>Vous n'avez soumis aucune demande.
                </div>
            @endif
        </div>
    </div>
    
    <div class="row">
        <div class="col-md-6">
            <div class="card mb-4">
                <div class="card-header bg-light">
                    <h5 class="mb-0">Demandes par type</h5>
                </div>
                <div class="card-body">
                    <canvas id="requestTypeChart" height="250"></canvas>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card mb-4">
                <div class="card-header bg-light">
                    <h5 class="mb-0">Demandes par statut</h5>
                </div>
                <div class="card-body">
                    <canvas id="requestStatusChart" height="250"></canvas>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    // Graphique des demandes par type
    const typeCtx = document.getElementById('requestTypeChart').getContext('2d');
    const typeChart = new Chart(typeCtx, {
        type: 'pie',
        data: {
            labels: ['Congé', 'Remboursement', 'Équipement', 'Heures supplémentaires', 'Autre'],
            datasets: [{
                data: [
                    {{ $requests->where('type', 'leave')->count() }},
                    {{ $requests->where('type', 'expense')->count() }},
                    {{ $requests->where('type', 'equipment')->count() }},
                    {{ $requests->where('type', 'overtime')->count() }},
                    {{ $requests->where('type', 'other')->count() }}
                ],
                backgroundColor: [
                    'rgba(23, 162, 184, 0.8)',
                    'rgba(255, 193, 7, 0.8)',
                    'rgba(0, 123, 255, 0.8)',
                    'rgba(128, 0, 128, 0.8)',
                    'rgba(108, 117, 125, 0.8)'
                ]
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: {
                    position: 'bottom'
                }
            }
        }
    });
    
    // Graphique des demandes par statut
    const statusCtx = document.getElementById('requestStatusChart').getContext('2d');
    const statusChart = new Chart(statusCtx, {
        type: 'doughnut',
        data: {
            labels: ['En attente', 'Approuvé', 'Rejeté'],
            datasets: [{
                data: [
                    {{ $requests->where('status', 'pending')->count() }},
                    {{ $requests->where('status', 'approved')->count() }},
                    {{ $requests->where('status', 'rejected')->count() }}
                ],
                backgroundColor: [
                    'rgba(255, 193, 7, 0.8)',
                    'rgba(40, 167, 69, 0.8)',
                    'rgba(220, 53, 69, 0.8)'
                ]
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: {
                    position: 'bottom'
                }
            }
        }
    });
</script>
@endsection