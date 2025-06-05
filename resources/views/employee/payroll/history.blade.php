@extends('employee.layouts.app')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1>Historique des Bulletins de Paie</h1>
        <div>
            <a href="{{ route('employee.payroll.index') }}" class="btn btn-secondary me-2">
                <i class="fas fa-arrow-left me-2"></i>Retour
            </a>
            @if($payrolls->count() > 1)
            <a href="{{ route('employee.payroll.compare') }}" class="btn btn-success">
                <i class="fas fa-chart-line me-2"></i>Comparer
            </a>
            @endif
        </div>
    </div>

    <!-- Filtres -->
    <div class="row mb-4">
        <div class="col-md-12">
            <div class="card">
                <div class="card-body">
                    <form method="GET" action="{{ route('employee.payroll.history') }}" class="row g-3">
                        <div class="col-md-3">
                            <label class="form-label">Année</label>
                            <select name="year" class="form-select" onchange="this.form.submit()">
                                @foreach($availableYears as $yearOption)
                                    <option value="{{ $yearOption }}" {{ $year == $yearOption ? 'selected' : '' }}>
                                        {{ $yearOption }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-9">
                            <label class="form-label">&nbsp;</label>
                            <div class="d-flex align-items-end">
                                <button type="button" class="btn btn-outline-secondary" onclick="exportData()">
                                    <i class="fas fa-download me-2"></i>Exporter
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Statistiques annuelles -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card bg-primary text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-white">Total Brut {{ $year }}</h6>
                            <h3 class="text-white">{{ number_format($yearStats['total_gross'] ?? 0, 0) }} DH</h3>
                        </div>
                        <i class="fas fa-euro-sign fa-2x opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-success text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-white">Total Net {{ $year }}</h6>
                            <h3 class="text-white">{{ number_format($yearStats['total_net'] ?? 0, 0) }} DH</h3>
                        </div>
                        <i class="fas fa-hand-holding-usd fa-2x opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-info text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-white">Heures Sup. {{ $year }}</h6>
                            <h3 class="text-white">{{ number_format($yearStats['total_overtime'] ?? 0, 0) }} DH</h3>
                        </div>
                        <i class="fas fa-business-time fa-2x opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-warning text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-white">Performance Moy.</h6>
                            <h3 class="text-white">{{ number_format($yearStats['avg_performance'] ?? 0, 1) }}%</h3>
                        </div>
                        <i class="fas fa-chart-line fa-2x opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Tableau historique -->
    <div class="card">
        <div class="card-header bg-light">
            <h5 class="mb-0">Bulletins de paie - {{ $year }}</h5>
        </div>
        <div class="card-body">
            @if($payrolls->count() > 0)
                <div class="table-responsive">
                    <table class="table table-hover" id="payrollTable">
                        <thead>
                            <tr>
                                <th>Période</th>
                                <th>Salaire Net</th>
                                <th>Salaire Brut</th>
                                <th>Ajustement</th>
                                <th>Heures Sup.</th>
                                <th>Performance</th>
                                <th>Statut</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($payrolls as $payroll)
                            <tr>
                                <td>
                                    <strong>{{ $payroll->formatted_period }}</strong>
                                    <br><small class="text-muted">{{ $payroll->period_start->format('d/m') }} - {{ $payroll->period_end->format('d/m/Y') }}</small>
                                </td>
                                <td>
                                    <strong class="text-success">{{ number_format($payroll->net_salary, 2) }} DH</strong>
                                </td>
                                <td>
                                    <span class="text-primary">{{ number_format($payroll->gross_salary, 2) }} DH</span>
                                    <br><small class="text-muted">Base: {{ number_format($payroll->base_salary, 2) }} DH</small>
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
                                    <span class="badge {{ $payroll->status_badge }}">{{ $payroll->status_label }}</span>
                                </td>
                                <td>
                                    <div class="btn-group" role="group">
                                        <a href="{{ route('employee.payroll.show', $payroll->id) }}" class="btn btn-sm btn-info" title="Voir détails">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <a href="{{ route('employee.payroll.download', $payroll->id) }}" class="btn btn-sm btn-outline-secondary" title="Télécharger PDF">
                                            <i class="fas fa-download"></i>
                                        </a>
                                        @if($payroll->performance_data)
                                        <button class="btn btn-sm btn-outline-primary" onclick="showPerformanceDetails({{ $payroll->id }})" title="Détails performance">
                                            <i class="fas fa-chart-bar"></i>
                                        </button>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <!-- Graphique d'évolution -->
                <div class="mt-4">
                    <div class="card">
                        <div class="card-header bg-light">
                            <h6 class="mb-0">Évolution des salaires - {{ $year }}</h6>
                        </div>
                        <div class="card-body">
                            <canvas id="salaryEvolutionChart" height="100"></canvas>
                        </div>
                    </div>
                </div>
            @else
                <div class="text-center py-4">
                    <i class="fas fa-file-invoice-dollar fa-3x text-muted mb-3"></i>
                    <p class="text-muted">Aucun bulletin de paie pour l'année {{ $year }}.</p>
                    @if($year == now()->year)
                        <small class="text-muted">Les bulletins sont générés automatiquement en fin de mois.</small>
                    @endif
                </div>
            @endif
        </div>
    </div>
</div>

<!-- Modal détails performance -->
<div class="modal fade" id="performanceModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Détails de Performance</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="performanceContent">
                <!-- Contenu chargé dynamiquement -->
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
// Graphique d'évolution des salaires
@if($payrolls->count() > 0)
const ctx = document.getElementById('salaryEvolutionChart').getContext('2d');
new Chart(ctx, {
    type: 'line',
    data: {
        labels: {!! json_encode($payrolls->pluck('formatted_period')->reverse()) !!},
        datasets: [{
            label: 'Salaire Net',
            data: {!! json_encode($payrolls->pluck('net_salary')->reverse()) !!},
            borderColor: 'rgba(40, 167, 69, 0.8)',
            backgroundColor: 'rgba(40, 167, 69, 0.1)',
            fill: true,
            tension: 0.4
        }, {
            label: 'Salaire Brut',
            data: {!! json_encode($payrolls->pluck('gross_salary')->reverse()) !!},
            borderColor: 'rgba(0, 123, 255, 0.8)',
            backgroundColor: 'rgba(0, 123, 255, 0.1)',
            fill: false,
            tension: 0.4
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
                        return context.dataset.label + ': ' + context.parsed.y.toLocaleString() + ' DH';
                    }
                }
            }
        }
    }
});
@endif

// Fonction pour afficher les détails de performance
function showPerformanceDetails(payrollId) {
    fetch(`/employee/payroll/${payrollId}/performance`)
        .then(response => response.json())
        .then(data => {
            let content = '<div class="row">';
            
            if (data.performance_data) {
                content += `
                    <div class="col-md-6">
                        <h6>Scores de Performance</h6>
                        <ul class="list-group">
                            <li class="list-group-item d-flex justify-content-between">
                                <span>Score Global</span>
                                <strong>${data.performance_data.overall_score}%</strong>
                            </li>
                            <li class="list-group-item d-flex justify-content-between">
                                <span>Taux de Présence</span>
                                <strong>${data.performance_data.attendance_rate}%</strong>
                            </li>
                            <li class="list-group-item d-flex justify-content-between">
                                <span>Tâches Complétées</span>
                                <strong>${data.performance_data.task_completion_rate}%</strong>
                            </li>
                        </ul>
                    </div>
                `;
            }
            
            if (data.adjustment_details && data.adjustment_details.length > 0) {
                content += `
                    <div class="col-md-6">
                        <h6>Critères d'Ajustement</h6>
                        <ul class="list-group">
                `;
                data.adjustment_details.forEach(detail => {
                    content += `<li class="list-group-item"><i class="fas fa-check-circle text-success me-2"></i>${detail}</li>`;
                });
                content += '</ul></div>';
            }
            
            content += '</div>';
            
            if (data.evaluation_report) {
                content += `
                    <hr>
                    <h6>Rapport d'Évaluation Source</h6>
                    <p><strong>${data.evaluation_report.title}</strong></p>
                    <p class="text-muted">${data.evaluation_report.summary}</p>
                `;
            }
            
            document.getElementById('performanceContent').innerHTML = content;
            new bootstrap.Modal(document.getElementById('performanceModal')).show();
        })
        .catch(error => {
            console.error('Erreur:', error);
            alert('Erreur lors du chargement des détails');
        });
}

// Fonction d'export
function exportData() {
    const year = '{{ $year }}';
    window.location.href = `/employee/payroll/export?year=${year}`;
}
</script>
@endsection