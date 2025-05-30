@extends('hr.layouts.app')

@section('content')
<div class="container-fluid">
    <h1 class="mt-2 mb-4">Mon Pointage - Administration RH</h1>
    
    <div class="row">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header bg-warning text-dark">
                    <h5 class="mb-0">Pointage du jour - {{ now()->format('d/m/Y') }}</h5>
                </div>
                <div class="card-body">
                    <div class="text-center mb-4">
                        <div class="avatar-placeholder bg-warning text-dark rounded-circle d-flex align-items-center justify-content-center mx-auto" style="width: 100px; height: 100px; font-size: 2.5rem;">
                            {{ substr($hrAdmin->name, 0, 1) }}
                        </div>
                        <h4 class="mt-3">{{ $hrAdmin->name }}</h4>
                        <p class="text-muted">Administrateur RH</p>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <div class="card h-100">
                                <div class="card-body text-center">
                                    <h6 class="text-muted">Heure d'arrivée</h6>
                                    <h3>{{ $todayAttendance && $todayAttendance->check_in ? $todayAttendance->check_in->format('H:i') : '--:--' }}</h3>
                                    
                                    @if(!$todayAttendance || !$todayAttendance->check_in)
                                        <form action="{{ route('hr.attendance.check-in') }}" method="POST">
                                            @csrf
                                            <button type="submit" class="btn btn-success mt-3">
                                                <i class="fas fa-sign-in-alt me-2"></i>Pointer mon arrivée
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <div class="card h-100">
                                <div class="card-body text-center">
                                    <h6 class="text-muted">Heure de départ</h6>
                                    <h3>{{ $todayAttendance && $todayAttendance->check_out ? $todayAttendance->check_out->format('H:i') : '--:--' }}</h3>
                                    
                                    @if($todayAttendance && $todayAttendance->check_in && !$todayAttendance->check_out)
                                        <form action="{{ route('hr.attendance.check-out') }}" method="POST">
                                            @csrf
                                            <button type="submit" class="btn btn-danger mt-3">
                                                <i class="fas fa-sign-out-alt me-2"></i>Pointer mon départ
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="alert {{ $todayAttendance ? ($todayAttendance->check_in && $todayAttendance->check_out ? 'alert-success' : 'alert-warning') : 'alert-danger' }} mt-3">
                        @if(!$todayAttendance)
                            <i class="fas fa-exclamation-circle me-2"></i>Vous n'avez pas encore pointé votre présence aujourd'hui.
                        @elseif(!$todayAttendance->check_out)
                            <i class="fas fa-info-circle me-2"></i>N'oubliez pas de pointer votre départ en fin de journée.
                        @else
                            <i class="fas fa-check-circle me-2"></i>Votre pointage est complet pour aujourd'hui.
                        @endif
                    </div>
                    
                    <div class="d-grid mt-4">
                        <a href="{{ route('hr.attendance.history') }}" class="btn btn-outline-warning">
                            <i class="fas fa-history me-2"></i>Voir mon historique de présence
                        </a>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-6">
            <div class="card">
                <div class="card-header bg-light">
                    <h5 class="mb-0">Mes horaires de la semaine</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Jour</th>
                                    <th>Date</th>
                                    <th>Arrivée</th>
                                    <th>Départ</th>
                                    <th>Statut</th>
                                </tr>
                            </thead>
                            <tbody>
                                @php
                                    $startOfWeek = now()->startOfWeek();
                                    $endOfWeek = now()->endOfWeek();
                                    
                                    for ($date = $startOfWeek; $date <= $endOfWeek; $date->addDay()) {
                                        $attendance = \App\Models\Attendance::where('user_id', $hrAdmin->id)
                                                                          ->where('date', $date->toDateString())
                                                                          ->first();
                                        
                                        $isToday = $date->isToday();
                                        $isPast = $date->isPast() && !$isToday;
                                        $isFuture = $date->isFuture();
                                        
                                        echo '<tr class="' . ($isToday ? 'table-active' : '') . '">';
                                        echo '<td>' . $date->translatedFormat('l') . '</td>';
                                        echo '<td>' . $date->format('d/m/Y') . '</td>';
                                        
                                        if ($attendance && $attendance->check_in) {
                                            echo '<td>' . $attendance->check_in->format('H:i') . '</td>';
                                        } elseif ($isPast) {
                                            echo '<td class="text-danger">Absent</td>';
                                        } elseif ($isToday) {
                                            echo '<td>--:--</td>';
                                        } else {
                                            echo '<td>--:--</td>';
                                        }
                                        
                                        if ($attendance && $attendance->check_out) {
                                            echo '<td>' . $attendance->check_out->format('H:i') . '</td>';
                                        } elseif ($isPast) {
                                            echo '<td class="text-danger">Absent</td>';
                                        } elseif ($isToday && $attendance && $attendance->check_in) {
                                            echo '<td>En cours</td>';
                                        } else {
                                            echo '<td>--:--</td>';
                                        }
                                        
                                        if ($attendance) {
                                            if ($attendance->status == 'present') {
                                                echo '<td><span class="badge bg-success">Présent</span></td>';
                                            } elseif ($attendance->status == 'absent') {
                                                echo '<td><span class="badge bg-danger">Absent</span></td>';
                                            } elseif ($attendance->status == 'late') {
                                                echo '<td><span class="badge bg-warning">En retard</span></td>';
                                            } else {
                                                echo '<td><span class="badge bg-secondary">' . ucfirst($attendance->status) . '</span></td>';
                                            }
                                        } elseif ($isPast) {
                                            echo '<td><span class="badge bg-danger">Absent</span></td>';
                                        } elseif ($isToday) {
                                            echo '<td><span class="badge bg-warning">À confirmer</span></td>';
                                        } else {
                                            echo '<td><span class="badge bg-secondary">À venir</span></td>';
                                        }
                                        
                                        echo '</tr>';
                                    }
                                @endphp
                            </tbody>
                        </table>
                    </div>
                    
                    <div class="alert alert-info mt-3">
                        <i class="fas fa-info-circle me-2"></i>Les horaires normaux de travail sont de 8h00 à 17h00 du lundi au vendredi.
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="row mt-4">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header bg-light">
                    <h5 class="mb-0">Actions rapides</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-3">
                            <a href="{{ route('hr.dashboard') }}" class="btn btn-warning w-100 mb-2">
                                <i class="fas fa-tachometer-alt me-2"></i>Tableau de bord RH
                            </a>
                        </div>
                        <div class="col-md-3">
                            <a href="{{ route('hr.users.index') }}" class="btn btn-primary w-100 mb-2">
                                <i class="fas fa-users me-2"></i>Gestion utilisateurs
                            </a>
                        </div>
                        <div class="col-md-3">
                            <a href="{{ route('hr.departments.index') }}" class="btn btn-info w-100 mb-2">
                                <i class="fas fa-building me-2"></i>Départements
                            </a>
                        </div>
                        <div class="col-md-3">
                            <a href="{{ route('hr.attendance.history') }}" class="btn btn-outline-warning w-100 mb-2">
                                <i class="fas fa-calendar-alt me-2"></i>Mon historique
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection