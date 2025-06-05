@extends('layouts.direction')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1><i class="fas fa-users-clock me-3"></i>Présences Globales</h1>
        <div>
            <a href="{{ route('direction.attendance.report') }}" class="btn btn-warning">
                <i class="fas fa-chart-bar me-2"></i>Rapport de présence
            </a>
        </div>
    </div>
    
    <!-- Filtre de date -->
    <div class="card mb-4">
        <div class="card-header bg-light">
            <form action="{{ route('direction.attendance') }}" method="GET" class="row g-2 align-items-center">
                <div class="col-md-4">
                    <div class="input-group">
                        <span class="input-group-text"><i class="fas fa-calendar"></i></span>
                        <input type="date" class="form-control" name="date" value="{{ $date }}" onchange="this.form.submit()">
                    </div>
                </div>
                <div class="col-md-8">
                    <div class="text-muted">
                        <i class="fas fa-info-circle me-2"></i>Visualisation de toutes les présences pour le {{ \Carbon\Carbon::parse($date)->format('d/m/Y') }}
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Statistiques globales -->
    <div class="row mb-4">
        <div class="col-lg-3 col-md-6 mb-3">
            <div class="card bg-success text-white h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-white">Présents</h6>
                            <h3 class="text-white">{{ $totalPresent }}</h3>
                        </div>
                        <i class="fas fa-user-check fa-2x opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-lg-3 col-md-6 mb-3">
            <div class="card bg-danger text-white h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-white">Absents</h6>
                            <h3 class="text-white">{{ $totalAbsent }}</h3>
                        </div>
                        <i class="fas fa-user-times fa-2x opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-lg-3 col-md-6 mb-3">
            <div class="card bg-warning text-white h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-white">En Retard</h6>
                            <h3 class="text-white">{{ $totalLate }}</h3>
                        </div>
                        <i class="fas fa-user-clock fa-2x opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-lg-3 col-md-6 mb-3">
            <div class="card bg-secondary text-white h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-white">Non Enregistrés</h6>
                            <h3 class="text-white">{{ $totalNotRecorded }}</h3>
                        </div>
                        <i class="fas fa-user-question fa-2x opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Détail des présences -->
    @if($attendances->count() > 0)
    <div class="card mb-4">
        <div class="card-header bg-light">
            <h5 class="mb-0">
                <i class="fas fa-check me-2"></i>Présences Enregistrées
                <span class="badge bg-primary ms-2">{{ $attendances->count() }}</span>
            </h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Employé</th>
                            <th>Département</th>
                            <th>Rôle</th>
                            <th>Heure d'arrivée</th>
                            <th>Heure de départ</th>
                            <th>Statut</th>
                            <th>Durée</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($attendances as $attendance)
                        <tr>
                            <td>
                                <strong>{{ $attendance->user->name }}</strong>
                                <br><small class="text-muted">{{ $attendance->user->email }}</small>
                            </td>
                            <td>{{ $attendance->user->department ? $attendance->user->department->name : 'N/A' }}</td>
                            <td>
                                @if($attendance->user->role == 'employee')
                                    <span class="badge bg-success">Employé</span>
                                @elseif($attendance->user->role == 'department_head')
                                    <span class="badge bg-primary">Chef Département</span>
                                @elseif($attendance->user->role == 'hr_admin')
                                    <span class="badge bg-warning">Admin RH</span>
                                @endif
                            </td>
                            <td>{{ $attendance->check_in ? $attendance->check_in->format('H:i') : '-' }}</td>
                            <td>{{ $attendance->check_out ? $attendance->check_out->format('H:i') : '-' }}</td>
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
                                    <span class="badge bg-secondary">{{ $attendance->status }}</span>
                                @endif
                            </td>
                            <td>
                                @if($attendance->check_in && $attendance->check_out)
                                    @php
                                        $duration = $attendance->check_in->diff($attendance->check_out);
                                        $hours = $duration->h;
                                        $minutes = $duration->i;
                                    @endphp
                                    <span class="text-muted">{{ $hours }}h {{ $minutes }}m</span>
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    @endif

    <!-- Utilisateurs sans présence -->
    @if($usersWithoutAttendance->count() > 0)
    <div class="card mb-4 border-warning">
        <div class="card-header bg-warning text-dark">
            <h5 class="mb-0">
                <i class="fas fa-exclamation-triangle me-2"></i>Sans présence enregistrée
                <span class="badge bg-dark ms-2">{{ $usersWithoutAttendance->count() }} personne(s)</span>
            </h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-warning">
                    <thead>
                        <tr>
                            <th>Employé</th>
                            <th>Département</th>
                            <th>Rôle</th>
                            <th>Email</th>
                            <th>Statut</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($usersWithoutAttendance as $user)
                        <tr>
                            <td><strong>{{ $user->name }}</strong></td>
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
                            <td>{{ $user->email }}</td>
                            <td><span class="badge bg-secondary">Non enregistré</span></td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    @endif

    <!-- Graphique -->
    <div class="row">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header bg-light">
                    <h5 class="mb-0"><i class="fas fa-chart-pie me-2"></i>Répartition Globale</h5>
                </div>
                <div class="card-body">
                    <canvas id="attendanceChart"></canvas>
                </div>
            </div>
        </div>
        
        <div class="col-md-6">
            <div class="card">
                <div class="card-header bg-light">
                    <h5 class="mb-0"><i class="fas fa-info me-2"></i>Résumé</h5>
                </div>
                <div class="card-body">
                    <div class="text-center">
                        <h4>{{ $totalUsers }} personnes au total</h4>
                        <p class="text-muted">{{ $totalPresent + $totalLate }} personnes présentes/en retard</p>
                        @php
                            $tauxPresence = $totalUsers > 0 ? round((($totalPresent + $totalLate) / $totalUsers) * 100, 1) : 0;
                        @endphp
                        <div class="progress mb-3" style="height: 30px;">
                            <div class="progress-bar bg-success" style="width: {{ $tauxPresence }}%">
                                {{ $tauxPresence }}% de présence
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    const attendanceCtx = document.getElementById('attendanceChart').getContext('2d');
    const attendanceChart = new Chart(attendanceCtx, {
        type: 'pie',
        data: {
            labels: ['Présents', 'Absents', 'En retard', 'Non enregistrés'],
            datasets: [{
                data: [{{ $totalPresent }}, {{ $totalAbsent }}, {{ $totalLate }}, {{ $totalNotRecorded }}],
                backgroundColor: [
                    'rgba(40, 167, 69, 0.8)',
                    'rgba(220, 53, 69, 0.8)',
                    'rgba(255, 193, 7, 0.8)',
                    'rgba(108, 117, 125, 0.8)'
                ],
                borderWidth: 2,
                borderColor: '#fff'
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'bottom',
                    labels: {
                        padding: 20,
                        usePointStyle: true
                    }
                }
            }
        }
    });
</script>
@endsection