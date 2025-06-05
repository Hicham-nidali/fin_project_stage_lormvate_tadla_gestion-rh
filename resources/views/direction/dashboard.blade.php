@extends('layouts.direction')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1><i class="fas fa-crown me-3"></i>Direction - Tableau de Bord</h1>
        <div class="text-muted">
            <i class="fas fa-calendar me-2"></i>{{ \Carbon\Carbon::now()->format('d/m/Y') }}
        </div>
    </div>
    
    <div class="row mb-4">
        <div class="col-lg-3 col-md-6 mb-3">
            <div class="card bg-dark text-white h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-white">Total Personnel</h6>
                            <h3 class="text-white">{{ $totalUsers }}</h3>
                            <small class="text-white-50">Employés + Chefs + Admin RH</small>
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
                            <h6 class="text-white">Présents Aujourd'hui</h6>
                            <h3 class="text-white">{{ $presentToday }}</h3>
                            @php
                                $taux = $totalUsers > 0 ? round(($presentToday / $totalUsers) * 100, 1) : 0;
                            @endphp
                            <small class="text-white-50">{{ $taux }}% du personnel</small>
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
                            <h3 class="text-white">{{ $absentToday }}</h3>
                            <small class="text-white-50">{{ $lateToday }} en retard</small>
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
                            <h6 class="text-white">Non Enregistrés</h6>
                            <h3 class="text-white">{{ $notRecorded }}</h3>
                            <small class="text-white-50">Sans pointage</small>
                        </div>
                        <i class="fas fa-user-question fa-2x opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header bg-light text-center">
                    <h5 class="mb-0"><i class="fas fa-users-clock me-2"></i>Consultation des Présences</h5>
                </div>
                <div class="card-body text-center">
                    <p class="text-muted mb-4">Accédez à la consultation globale des présences de tout le personnel</p>
                    <a href="{{ route('direction.attendance') }}" class="btn btn-dark btn-lg">
                        <i class="fas fa-eye me-2"></i>Consulter les Présences Globales
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="row mt-4">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header bg-light">
                    <h5 class="mb-0"><i class="fas fa-chart-pie me-2"></i>Répartition des Présences</h5>
                </div>
                <div class="card-body">
                    <canvas id="attendanceChart" height="200"></canvas>
                </div>
            </div>
        </div>
        
        <div class="col-md-6">
            <div class="card">
                <div class="card-header bg-light">
                    <h5 class="mb-0"><i class="fas fa-info-circle me-2"></i>Informations</h5>
                </div>
                <div class="card-body">
                    <div class="alert alert-info">
                        <h6><i class="fas fa-crown me-2"></i>Interface Direction</h6>
                        <p class="mb-2">Cette interface vous permet de :</p>
                        <ul class="mb-0">
                            <li>Consulter les présences de tout le personnel</li>
                            <li>Voir les statistiques globales en temps réel</li>
                            <li>Filtrer par date pour l'historique</li>
                        </ul>
                    </div>
                    
                    <div class="mt-3">
                        <small class="text-muted">
                            <i class="fas fa-clock me-1"></i>Dernière mise à jour : {{ \Carbon\Carbon::now()->format('H:i') }}
                        </small>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    const ctx = document.getElementById('attendanceChart').getContext('2d');
    const chart = new Chart(ctx, {
        type: 'doughnut',
        data: {
            labels: ['Présents', 'Absents', 'En retard', 'Non enregistrés'],
            datasets: [{
                data: [{{ $presentToday }}, {{ $absentToday }}, {{ $lateToday }}, {{ $notRecorded }}],
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