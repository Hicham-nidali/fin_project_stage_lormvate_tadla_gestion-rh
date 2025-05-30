@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1>Mon Historique de Présence - Chef de Département</h1>
        <a href="{{ route('department-head.attendance.index') }}" class="btn btn-success">
            <i class="fas fa-clock me-2"></i>Pointage du jour
        </a>
    </div>
    
    <div class="card mb-4">
        <div class="card-header bg-light">
            <form action="{{ route('department-head.attendance.history') }}" method="GET" class="row g-3 align-items-center">
                <div class="col-md-4">
                    <div class="input-group">
                        <span class="input-group-text">Du</span>
                        <input type="date" class="form-control" name="start_date" value="{{ $startDate }}">
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="input-group">
                        <span class="input-group-text">Au</span>
                        <input type="date" class="form-control" name="end_date" value="{{ $endDate }}">
                    </div>
                </div>
                <div class="col-md-4">
                    <button type="submit" class="btn btn-primary w-100">Afficher</button>
                </div>
            </form>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Jour</th>
                            <th>Arrivée</th>
                            <th>Départ</th>
                            <th>Durée</th>
                            <th>Statut</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($attendanceHistory as $attendance)
                        <tr>
                            <td>{{ $attendance->date->format('d/m/Y') }}</td>
                            <td>{{ $attendance->date->translatedFormat('l') }}</td>
                            <td>{{ $attendance->check_in ? $attendance->check_in->format('H:i') : '-' }}</td>
                            <td>{{ $attendance->check_out ? $attendance->check_out->format('H:i') : '-' }}</td>
                            <td>
                                @if($attendance->check_in && $attendance->check_out)
                                    @php
                                        $duration = $attendance->check_in->diffInHours($attendance->check_out) . 'h ' . 
                                                  ($attendance->check_in->diffInMinutes($attendance->check_out) % 60) . 'm';
                                    @endphp
                                    {{ $duration }}
                                @else
                                    -
                                @endif
                            </td>
                            <td>
                                @if($attendance->status == 'present')
                                    <span class="badge bg-success">Présent</span>
                                @elseif($attendance->status == 'absent')
                                    <span class="badge bg-danger">Absent</span>
                                @elseif($attendance->status == 'late')
                                    <span class="badge bg-warning">En retard</span>
                                @elseif($attendance->status == 'half_day')
                                    <span class="badge bg-info">Demi-journée</span>
                                @else
                                    <span class="badge bg-secondary">{{ ucfirst($attendance->status) }}</span>
                                @endif
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            
            @if($attendanceHistory->isEmpty())
                <div class="alert alert-info">
                    <i class="fas fa-info-circle me-2"></i>Aucune donnée de présence pour la période sélectionnée.
                </div>
            @endif
        </div>
    </div>
    
    <div class="row">
        <div class="col-md-6">
            <div class="card mb-4">
                <div class="card-header bg-light">
                    <h5 class="mb-0">Résumé</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <div class="card bg-success text-white">
                                <div class="card-body py-2">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <h6 class="text-white mb-0">Jours présent</h6>
                                            <h3 class="text-white mb-0">{{ $attendanceHistory->where('status', 'present')->count() }}</h3>
                                        </div>
                                        <i class="fas fa-calendar-check fa-2x opacity-50"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <div class="card bg-danger text-white">
                                <div class="card-body py-2">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <h6 class="text-white mb-0">Jours absent</h6>
                                            <h3 class="text-white mb-0">{{ $attendanceHistory->where('status', 'absent')->count() }}</h3>
                                        </div>
                                        <i class="fas fa-calendar-times fa-2x opacity-50"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <div class="card bg-warning text-white">
                                <div class="card-body py-2">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <h6 class="text-white mb-0">Retards</h6>
                                            <h3 class="text-white mb-0">{{ $attendanceHistory->where('status', 'late')->count() }}</h3>
                                        </div>
                                        <i class="fas fa-clock fa-2x opacity-50"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <div class="card bg-primary text-white">
                                <div class="card-body py-2">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <h6 class="text-white mb-0">Heures travaillées</h6>
                                            <h3 class="text-white mb-0">
                                                @php
                                                    $totalMinutes = 0;
                                                    foreach($attendanceHistory as $attendance) {
                                                        if($attendance->check_in && $attendance->check_out) {
                                                            $totalMinutes += $attendance->check_in->diffInMinutes($attendance->check_out);
                                                        }
                                                    }
                                                    $hours = floor($totalMinutes / 60);
                                                    $minutes = $totalMinutes % 60;
                                                    echo $hours . 'h ' . $minutes . 'm';
                                                @endphp
                                            </h3>
                                        </div>
                                        <i class="fas fa-business-time fa-2x opacity-50"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card">
                <div class="card-header bg-light">
                    <h5 class="mb-0">Actions</h5>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-3">
                        <a href="{{ route('attendance.index') }}" class="btn btn-primary">
                            <i class="fas fa-users me-2"></i>Gérer présences équipe
                        </a>
                        <a href="{{ route('attendance.report') }}" class="btn btn-info">
                            <i class="fas fa-chart-bar me-2"></i>Rapport équipe
                        </a>
                        <a href="{{ route('dashboard') }}" class="btn btn-success">
                            <i class="fas fa-tachometer-alt me-2"></i>Tableau de bord
                        </a>
                        <button class="btn btn-outline-primary">
                            <i class="fas fa-file-export me-2"></i>Exporter mes données
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection