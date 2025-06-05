@extends('layouts.direction')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1><i class="fas fa-chart-bar me-3"></i>Rapport de Présence</h1>
        <a href="{{ route('direction.attendance') }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left me-2"></i>Retour aux présences
        </a>
    </div>
    
    <!-- Filtre de période -->
    <div class="card mb-4">
        <div class="card-header bg-light">
            <form action="{{ route('direction.attendance.report') }}" method="GET" class="row g-2 align-items-center">
                <div class="col-md-3">
                    <label for="start_date" class="form-label">Date début</label>
                    <input type="date" class="form-control" name="start_date" value="{{ $startDate }}">
                </div>
                <div class="col-md-3">
                    <label for="end_date" class="form-label">Date fin</label>
                    <input type="date" class="form-control" name="end_date" value="{{ $endDate }}">
                </div>
                <div class="col-md-3">
                    <label class="form-label">&nbsp;</label>
                    <button type="submit" class="btn btn-primary d-block">
                        <i class="fas fa-search me-2"></i>Générer rapport
                    </button>
                </div>
                <div class="col-md-3">
                    <label class="form-label">&nbsp;</label>
                    <div class="text-muted">
                        Période : {{ \Carbon\Carbon::parse($startDate)->format('d/m/Y') }} - {{ \Carbon\Carbon::parse($endDate)->format('d/m/Y') }}
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Statistiques globales -->
    <div class="row mb-4">
        <div class="col-lg-3 col-md-6 mb-3">
            <div class="card bg-primary text-white h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-white">Total Jours</h6>
                            <h3 class="text-white">{{ $totalDays }}</h3>
                            <small class="text-white-50">Jours analysés</small>
                        </div>
                        <i class="fas fa-calendar fa-2x opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-lg-3 col-md-6 mb-3">
            <div class="card bg-info text-white h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-white">Total Personnel</h6>
                            <h3 class="text-white">{{ $totalUsers }}</h3>
                            <small class="text-white-50">Utilisateurs actifs</small>
                        </div>
                        <i class="fas fa-users fa-2x opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-lg-3 col-md-6 mb-3">
            <div class="card bg-success text-white h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-white">Présences Enregistrées</h6>
                            <h3 class="text-white">{{ $attendanceData->count() }}</h3>
                            <small class="text-white-50">Sur la période</small>
                        </div>
                        <i class="fas fa-check fa-2x opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-lg-3 col-md-6 mb-3">
            <div class="card bg-warning text-white h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-white">Taux Global</h6>
                            <h3 class="text-white">{{ $globalAttendanceRate }}%</h3>
                            <small class="text-white-50">Taux de présence</small>
                        </div>
                        <i class="fas fa-percentage fa-2x opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Données détaillées -->
    <div class="card">
        <div class="card-header bg-light">
            <h5 class="mb-0"><i class="fas fa-table me-2"></i>Détail des Présences par Personne</h5>
        </div>
        <div class="card-body">
            @if($attendanceData->count() > 0)
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Employé</th>
                                <th>Département</th>
                                <th>Rôle</th>
                                <th>Présents</th>
                                <th>Absents</th>
                                <th>Retards</th>
                                <th>Taux Présence</th>
                            </tr>
                        </thead>
                        <tbody>
                            @php
                                $groupedData = $attendanceData->groupBy('user_id');
                            @endphp
                            @foreach($groupedData as $userId => $userAttendances)
                                @php
                                    $user = $userAttendances->first()->user;
                                    $presentCount = $userAttendances->where('status', 'present')->count();
                                    $absentCount = $userAttendances->where('status', 'absent')->count();
                                    $lateCount = $userAttendances->where('status', 'late')->count();
                                    $totalAttendances = $userAttendances->count();
                                    $attendanceRate = $totalAttendances > 0 ? round((($presentCount + $lateCount) / $totalAttendances) * 100, 1) : 0;
                                @endphp
                                <tr>
                                    <td>
                                        <strong>{{ $user->name }}</strong>
                                        <br><small class="text-muted">{{ $user->email }}</small>
                                    </td>
                                    <td>{{ $user->department ? $user->department->name : 'N/A' }}</td>
                                    <td>
                                        @if($user->role == 'employee')
                                            <span class="badge bg-success">Employé</span>
                                        @elseif($user->role == 'department_head')
                                            <span class="badge bg-primary">Chef Département</span>
                                        @elseif($user->role == 'hr_admin')
                                            <span class="badge bg-warning">Admin RH</span>
                                        @endif
                                    </td>
                                    <td><span class="badge bg-success">{{ $presentCount }}</span></td>
                                    <td><span class="badge bg-danger">{{ $absentCount }}</span></td>
                                    <td><span class="badge bg-warning">{{ $lateCount }}</span></td>
                                    <td>
                                        <div class="progress" style="height: 20px;">
                                            <div class="progress-bar 
                                                @if($attendanceRate >= 80) bg-success 
                                                @elseif($attendanceRate >= 60) bg-warning 
                                                @else bg-danger @endif" 
                                                role="progressbar" 
                                                style="width: {{ $attendanceRate }}%">
                                                {{ $attendanceRate }}%
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="text-center py-4">
                    <i class="fas fa-exclamation-circle fa-3x text-muted mb-3"></i>
                    <p class="text-muted">Aucune donnée de présence trouvée pour cette période.</p>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection