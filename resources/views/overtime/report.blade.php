@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1>Rapport des Heures Supplémentaires</h1>
        <div>
            <a href="{{ route('overtime.index') }}" class="btn btn-secondary me-2">
                <i class="fas fa-arrow-left me-2"></i>Retour
            </a>
            <button type="button" class="btn btn-primary" onclick="window.print()">
                <i class="fas fa-print me-2"></i>Imprimer
            </button>
        </div>
    </div>
    
    <div class="card mb-4">
        <div class="card-header bg-light">
            <form action="{{ route('overtime.report') }}" method="GET" class="row g-3 align-items-center">
                <div class="col-md-4">
                    <label for="start_date" class="form-label">Du</label>
                    <input type="date" class="form-control" name="start_date" value="{{ $startDate }}">
                </div>
                <div class="col-md-4">
                    <label for="end_date" class="form-label">Au</label>
                    <input type="date" class="form-control" name="end_date" value="{{ $endDate }}">
                </div>
                <div class="col-md-4">
                    <button type="submit" class="btn btn-primary w-100">Générer rapport</button>
                </div>
            </form>
        </div>
    </div>
    
    <!-- Statistiques globales -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card bg-primary text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-white">Total heures</h6>
                            <h3 class="text-white">{{ number_format($totalOvertimeHours, 1) }}h</h3>
                        </div>
                        <i class="fas fa-clock fa-2x opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-success text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-white">Demandes approuvées</h6>
                            <h3 class="text-white">{{ $totalOvertimeRequests }}</h3>
                        </div>
                        <i class="fas fa-check-circle fa-2x opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-info text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-white">Moyenne/demande</h6>
                            <h3 class="text-white">{{ number_format($averageHoursPerRequest, 1) }}h</h3>
                        </div>
                        <i class="fas fa-chart-bar fa-2x opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-warning text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-white">Employés concernés</h6>
                            <h3 class="text-white">{{ $overtimeByEmployee->count() }}</h3>
                        </div>
                        <i class="fas fa-users fa-2x opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Graphique -->
    <div class="row mb-4">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header bg-light">
                    <h5 class="mb-0">Répartition des heures par employé</h5>
                </div>
                <div class="card-body">
                    <canvas id="overtimeChart" height="100"></canvas>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Détail par employé -->
    <div class="card">
        <div class="card-header bg-light">
            <h5 class="mb-0">Détail par employé - Période du {{ \Carbon\Carbon::parse($startDate)->format('d/m/Y') }} au {{ \Carbon\Carbon::parse($endDate)->format('d/m/Y') }}</h5>
        </div>
        <div class="card-body">
            @if($overtimeByEmployee->count() > 0)
                @foreach($overtimeByEmployee as $employeeData)
                <div class="card mb-3">
                    <div class="card-header">
                        <div class="d-flex justify-content-between align-items-center">
                            <h6 class="mb-0">{{ $employeeData['user']->name }}</h6>
                            <div>
                                <span class="badge bg-primary">{{ number_format($employeeData['total_hours'], 1) }}h totales</span>
                                <span class="badge bg-secondary">{{ $employeeData['total_records'] }} demandes</span>
                                @php
                                    $avgHours = $employeeData['total_records'] > 0 ? $employeeData['total_hours'] / $employeeData['total_records'] : 0;
                                @endphp
                                <span class="badge bg-info">{{ number_format($avgHours, 1) }}h/demande</span>
                            </div>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-sm">
                                <thead>
                                    <tr>
                                        <th>Date</th>
                                        <th>Horaires</th>
                                        <th>Heures</th>
                                        <th>Type</th>
                                        <th>Taux</th>
                                        <th>Raison</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($employeeData['records'] as $record)
                                    <tr>
                                        <td>{{ $record->overtime_date->format('d/m/Y') }}</td>
                                        <td>
                                            {{ \Carbon\Carbon::parse($record->start_time)->format('H:i') }} - 
                                            {{ \Carbon\Carbon::parse($record->end_time)->format('H:i') }}
                                        </td>
                                        <td><strong>{{ $record->hours_approved }}h</strong></td>
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
                                            @php
                                                $rate = is_array($metadata) ? ($metadata['overtime_rate'] ?? 1.25) : 1.25;
                                            @endphp
                                            {{ ($rate * 100) }}%
                                        </td>
                                        <td>{{ Str::limit($record->reason, 50) }}</td>
                                    </tr>
                                    @endforeach
                                </tbody>
                                <tfoot>
                                    <tr class="table-primary">
                                        <td colspan="2"><strong>Total pour {{ $employeeData['user']->name }}</strong></td>
                                        <td><strong>{{ number_format($employeeData['total_hours'], 1) }}h</strong></td>
                                        <td colspan="3"><strong>{{ $employeeData['total_records'] }} demandes</strong></td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>
                </div>
                @endforeach
                
                <!-- Résumé final -->
                <div class="card bg-light">
                    <div class="card-body">
                        <div class="row text-center">
                            <div class="col-md-3">
                                <h4 class="text-primary">{{ number_format($totalOvertimeHours, 1) }}h</h4>
                                <small>Total heures</small>
                            </div>
                            <div class="col-md-3">
                                <h4 class="text-success">{{ $totalOvertimeRequests }}</h4>
                                <small>Total demandes</small>
                            </div>
                            <div class="col-md-3">
                                <h4 class="text-info">{{ number_format($averageHoursPerRequest, 1) }}h</h4>
                                <small>Moyenne par demande</small>
                            </div>
                            <div class="col-md-3">
                                <h4 class="text-warning">{{ $overtimeByEmployee->count() }}</h4>
                                <small>Employés concernés</small>
                            </div>
                        </div>
                    </div>
                </div>
            @else
                <div class="alert alert-info">
                    <i class="fas fa-info-circle me-2"></i>Aucune heure supplémentaire approuvée pour la période sélectionnée.
                </div>
            @endif
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    // Graphique des heures supplémentaires par employé
    const ctx = document.getElementById('overtimeChart').getContext('2d');
    
    const employeeNames = @json($overtimeByEmployee->pluck('user.name'));
    const employeeHours = @json($overtimeByEmployee->pluck('total_hours'));
    
    const chart = new Chart(ctx, {
        type: 'bar',
        data: {
            labels: employeeNames,
            datasets: [{
                label: 'Heures supplémentaires',
                data: employeeHours,
                backgroundColor: 'rgba(0, 123, 255, 0.8)',
                borderColor: 'rgba(0, 123, 255, 1)',
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            scales: {
                y: {
                    beginAtZero: true,
                    title: {
                        display: true,
                        text: 'Heures'
                    }
                },
                x: {
                    title: {
                        display: true,
                        text: 'Employés'
                    }
                }
            },
            plugins: {
                legend: {
                    display: false
                },
                title: {
                    display: true,
                    text: 'Heures supplémentaires par employé'
                }
            }
        }
    });
</script>
@endsection