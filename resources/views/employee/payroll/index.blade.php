@extends('employee.layouts.app')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1>Mes Bulletins de Paie</h1>
        <div>
            <a href="{{ route('employee.payroll.history') }}" class="btn btn-info me-2">
                <i class="fas fa-history me-2"></i>Historique
            </a>
            @if($recentPayrolls->count() > 1)
            <a href="{{ route('employee.payroll.compare') }}" class="btn btn-success">
                <i class="fas fa-chart-line me-2"></i>Comparer
            </a>
            @endif
        </div>
    </div>

    <!-- Informations actuelles -->
    <div class="row mb-4">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header bg-light">
                    <h5 class="mb-0">Bulletin du mois en cours</h5>
                </div>
                <div class="card-body">
                    @if($currentPayroll)
                        <div class="row">
                            <div class="col-md-6">
                                <h3 class="text-success">{{ number_format($currentPayroll->net_salary, 2) }} DH</h3>
                                <p class="text-muted">Salaire net - {{ $currentPayroll->formatted_period }}</p>
                                
                                @if($currentPayroll->hasAdjustment())
                                <div class="alert {{ $currentPayroll->isPositiveAdjustment() ? 'alert-success' : 'alert-warning' }} py-2">
                                    <small>
                                        <i class="fas {{ $currentPayroll->isPositiveAdjustment() ? 'fa-arrow-up' : 'fa-arrow-down' }} me-1"></i>
                                        Ajustement de {{ $currentPayroll->formatted_adjustment }} 
                                        ({{ number_format($currentPayroll->adjustment_amount, 2) }} DH)
                                    </small>
                                </div>
                                @endif

                                @if($currentPayroll->hasOvertime())
                                <div class="alert alert-info py-2">
                                    <small>
                                        <i class="fas fa-business-time me-1"></i>
                                        {{ $currentPayroll->overtime_hours }}h supplémentaires 
                                        ({{ number_format($currentPayroll->overtime_amount, 2) }} DH)
                                    </small>
                                </div>
                                @endif
                            </div>
                            <div class="col-md-6">
                                <div class="mb-2">
                                    <small class="text-muted">Salaire de base:</small>
                                    <strong class="float-end">{{ number_format($currentPayroll->base_salary, 2) }} DH</strong>
                                </div>
                                <div class="mb-2">
                                    <small class="text-muted">Ajustements:</small>
                                    <strong class="float-end">{{ number_format($currentPayroll->adjustment_amount, 2) }} DH</strong>
                                </div>
                                <div class="mb-2">
                                    <small class="text-muted">Heures supplémentaires:</small>
                                    <strong class="float-end">{{ number_format($currentPayroll->overtime_amount, 2) }} DH</strong>
                                </div>
                                <div class="mb-2">
                                    <small class="text-muted">Salaire brut:</small>
                                    <strong class="float-end">{{ number_format($currentPayroll->gross_salary, 2) }} DH</strong>
                                </div>
                                <div class="mb-2">
                                    <small class="text-muted">Déductions:</small>
                                    <strong class="float-end text-danger">-{{ number_format($currentPayroll->deductions, 2) }} DH</strong>
                                </div>
                                <hr>
                                <div>
                                    <strong>Salaire net:</strong>
                                    <strong class="float-end text-success">{{ number_format($currentPayroll->net_salary, 2) }} DH</strong>
                                </div>
                            </div>
                        </div>
                        
                        <div class="mt-3">
                            <a href="{{ route('employee.payroll.show', $currentPayroll->id) }}" class="btn btn-primary me-2">
                                <i class="fas fa-eye me-2"></i>Voir détails
                            </a>
                            <a href="{{ route('employee.payroll.download', $currentPayroll->id) }}" class="btn btn-outline-secondary">
                                <i class="fas fa-download me-2"></i>Télécharger PDF
                            </a>
                        </div>
                    @else
                        <div class="text-center py-4">
                            <i class="fas fa-file-invoice-dollar fa-3x text-muted mb-3"></i>
                            <p class="text-muted">Votre bulletin pour ce mois n'est pas encore disponible.</p>
                            <small class="text-muted">Il sera généré automatiquement en fin de mois.</small>
                        </div>
                    @endif
                </div>
            </div>
        </div>
        
        <div class="col-md-4">
            <div class="card">
                <div class="card-header bg-light">
                    <h5 class="mb-0">Salaire actuel</h5>
                </div>
                <div class="card-body">
                    @if($currentSalary)
                        <h3 class="text-primary">{{ number_format($currentSalary->base_salary, 2) }} DH</h3>
                        <p class="text-muted mb-2">Salaire de base mensuel</p>
                        
                        <hr>
                        
                        <div class="mb-2">
                            <small class="text-muted">Effectif depuis:</small>
                            <strong class="float-end">{{ $currentSalary->effective_from->format('d/m/Y') }}</strong>
                        </div>
                        
                        @if($currentSalary->notes)
                        <div class="mt-3">
                            <small class="text-muted">Notes:</small>
                            <p class="small">{{ $currentSalary->notes }}</p>
                        </div>
                        @endif
                    @else
                        <div class="text-center py-3">
                            <i class="fas fa-exclamation-triangle fa-2x text-warning mb-2"></i>
                            <p class="text-muted small">Aucun salaire défini</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Statistiques -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card bg-primary text-white h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-white">Total Bulletins</h6>
                            <h3 class="text-white">{{ $stats['total_payrolls'] }}</h3>
                        </div>
                        <i class="fas fa-file-invoice-dollar fa-2x opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-success text-white h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-white">Salaire Net Moyen</h6>
                            <h3 class="text-white">{{ number_format($stats['avg_net_salary'] ?? 0, 0) }} DH</h3>
                        </div>
                        <i class="fas fa-euro-sign fa-2x opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-info text-white h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-white">Total Heures Sup.</h6>
                            <h3 class="text-white">{{ number_format($stats['total_overtime'] ?? 0, 0) }} DH</h3>
                        </div>
                        <i class="fas fa-business-time fa-2x opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-warning text-white h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-white">Ajustements +</h6>
                            <h3 class="text-white">{{ $stats['positive_adjustments'] }}</h3>
                        </div>
                        <i class="fas fa-arrow-up fa-2x opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Historique récent -->
    <div class="card">
        <div class="card-header bg-light d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Bulletins récents (12 derniers mois)</h5>
            <a href="{{ route('employee.payroll.history') }}" class="btn btn-sm btn-primary">Voir tout l'historique</a>
        </div>
        <div class="card-body">
            @if($recentPayrolls->count() > 0)
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Période</th>
                                <th>Salaire Net</th>
                                <th>Ajustement</th>
                                <th>Heures Sup.</th>
                                <th>Performance</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($recentPayrolls as $payroll)
                            <tr>
                                <td>
                                    <strong>{{ $payroll->formatted_period }}</strong>
                                    <br><small class="text-muted">{{ $payroll->period_start->format('d/m') }} - {{ $payroll->period_end->format('d/m/Y') }}</small>
                                </td>
                                <td>
                                    <strong class="text-success">{{ number_format($payroll->net_salary, 2) }} DH</strong>
                                    <br><small class="text-muted">Brut: {{ number_format($payroll->gross_salary, 2) }} DH</small>
                                </td>
                                <td>
                                    @if($payroll->hasAdjustment())
                                        <span class="badge {{ $payroll->isPositiveAdjustment() ? 'bg-success' : 'bg-warning' }}">
                                            {{ $payroll->formatted_adjustment }}
                                        </span>
                                        <br><small class="text-muted">{{ number_format($payroll->adjustment_amount, 2) }} DH</small>
                                    @else
                                        <span class="text-muted">Aucun</span>
                                    @endif
                                </td>
                                <td>
                                    @if($payroll->hasOvertime())
                                        <strong>{{ $payroll->overtime_hours }}h</strong>
                                        <br><small class="text-muted">{{ number_format($payroll->overtime_amount, 2) }} DH</small>
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                                <td>
                                    @if($payroll->performance_score)
                                        <div class="d-flex align-items-center">
                                            <div class="progress me-2" style="width: 60px; height: 8px;">
                                                <div class="progress-bar 
                                                    @if($payroll->performance_score >= 80) bg-success
                                                    @elseif($payroll->performance_score >= 60) bg-warning  
                                                    @else bg-danger @endif" 
                                                    style="width: {{ $payroll->performance_score }}%">
                                                </div>
                                            </div>
                                            <small>{{ $payroll->performance_score }}%</small>
                                        </div>
                                    @else
                                        <span class="text-muted">N/A</span>
                                    @endif
                                </td>
                                <td>
                                    <a href="{{ route('employee.payroll.show', $payroll->id) }}" class="btn btn-sm btn-info">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="{{ route('employee.payroll.download', $payroll->id) }}" class="btn btn-sm btn-outline-secondary">
                                        <i class="fas fa-download"></i>
                                    </a>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="text-center py-4">
                    <i class="fas fa-file-invoice-dollar fa-3x text-muted mb-3"></i>
                    <p class="text-muted">Aucun bulletin de paie disponible.</p>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
@if($recentPayrolls->count() > 1)
const ctx = document.getElementById('salaryChart');
if (ctx) {
    new Chart(ctx, {
        type: 'line',
        data: {
            labels: {!! json_encode($recentPayrolls->pluck('formatted_period')->reverse()) !!},
            datasets: [{
                label: 'Salaire Net',
                data: {!! json_encode($recentPayrolls->pluck('net_salary')->reverse()) !!},
                borderColor: 'rgba(40, 167, 69, 0.8)',
                backgroundColor: 'rgba(40, 167, 69, 0.1)',
                fill: true
            }]
        },
        options: {
            responsive: true,
            scales: {
                y: {
                    beginAtZero: false,
                    ticks: {
                        callback: function(value) {
                            return value.toLocaleString() + ' DH';
                        }
                    }
                }
            },
            plugins: {
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            return 'Salaire Net: ' + context.parsed.y.toLocaleString() + ' DH';
                        }
                    }
                }
            }
        }
    });
}
@endif
</script>
@endsection