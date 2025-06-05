@extends('hr.layouts.app')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1>Gestion des Paiements - {{ \Carbon\Carbon::createFromFormat('Y-m', $currentPeriod)->format('F Y') }}</h1>
        <div>
            <a href="{{ route('hr.payroll.reports') }}" class="btn btn-info me-2">
                <i class="fas fa-chart-bar me-2"></i>Rapports
            </a>
            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#calculatePayrollModal">
                <i class="fas fa-calculator me-2"></i>Calculer Paie
            </button>
        </div>
    </div>

    @if($pendingApprovals > 0)
    <div class="alert alert-warning alert-dismissible fade show">
        <i class="fas fa-exclamation-triangle me-2"></i>
        <strong>{{ $pendingApprovals }} bulletin(s)</strong> en attente d'approbation.
        <a href="{{ route('hr.payroll.index', ['status' => 'calculated']) }}" class="alert-link">Voir la liste</a>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    @endif

    @if($totalEmployees > $employeesWithSalary)
    <div class="alert alert-info alert-dismissible fade show">
        <i class="fas fa-info-circle me-2"></i>
        <strong>{{ $totalEmployees - $employeesWithSalary }} employé(s)</strong> sans salaire défini.
        <a href="{{ route('hr.payroll.salaries.index') }}" class="alert-link">Gérer les salaires</a>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    @endif

    <!-- Statistiques principales -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card bg-primary text-white h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-white">Total Brut</h6>
                            <h3 class="text-white">{{ number_format($summary['total_gross'] ?? 0, 0) }} DH</h3>
                        </div>
                        <i class="fas fa-money-bill-wave fa-2x opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-success text-white h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-white">Total Net</h6>
                            <h3 class="text-white">{{ number_format($summary['total_net'] ?? 0, 0) }} DH</h3>
                        </div>
                        <i class="fas fa-hand-holding-usd fa-2x opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-warning text-white h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-white">Ajustements</h6>
                            <h3 class="text-white">{{ number_format($summary['total_adjustments'] ?? 0, 0) }} DH</h3>
                        </div>
                        <i class="fas fa-chart-line fa-2x opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-info text-white h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-white">Employés</h6>
                            <h3 class="text-white">{{ $summary['total_employees'] ?? 0 }}</h3>
                        </div>
                        <i class="fas fa-users fa-2x opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Actions rapides -->
    <div class="row mb-4">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header bg-light">
                    <h5 class="mb-0">Actions rapides</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-3">
                            <a href="{{ route('hr.payroll.salaries.index') }}" class="btn btn-primary w-100 mb-2">
                                <i class="fas fa-money-bill-wave me-2"></i>Gérer Salaires
                            </a>
                        </div>
                        <div class="col-md-3">
                            <a href="{{ route('hr.payroll.index') }}" class="btn btn-success w-100 mb-2">
                                <i class="fas fa-file-invoice-dollar me-2"></i>Voir Bulletins
                            </a>
                        </div>
                        <div class="col-md-3">
                            <button type="button" class="btn btn-warning w-100 mb-2" data-bs-toggle="modal" data-bs-target="#bulkApproveModal">
                                <i class="fas fa-check-double me-2"></i>Approbation en lot
                            </button>
                        </div>
                        <div class="col-md-3">
                            <a href="{{ route('hr.payroll.export', ['period' => $currentPeriod]) }}" class="btn btn-info w-100 mb-2">
                                <i class="fas fa-download me-2"></i>Exporter
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Bulletins récents -->
    <div class="row">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header bg-light d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Bulletins récents - {{ \Carbon\Carbon::createFromFormat('Y-m', $currentPeriod)->format('F Y') }}</h5>
                    <a href="{{ route('hr.payroll.index') }}" class="btn btn-sm btn-primary">Voir tout</a>
                </div>
                <div class="card-body">
                    @if($recentPayrolls->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Employé</th>
                                        <th>Département</th>
                                        <th>Salaire Net</th>
                                        <th>Ajustement</th>
                                        <th>Statut</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($recentPayrolls as $payroll)
                                    <tr>
                                        <td>{{ $payroll->user->name }}</td>
                                        <td>{{ $payroll->department->name }}</td>
                                        <td><strong>{{ number_format($payroll->net_salary, 2) }} DH</strong></td>
                                        <td>
                                            @if($payroll->hasAdjustment())
                                                <span class="badge {{ $payroll->isPositiveAdjustment() ? 'bg-success' : 'bg-danger' }}">
                                                    {{ $payroll->formatted_adjustment }}
                                                </span>
                                            @else
                                                <span class="text-muted">Aucun</span>
                                            @endif
                                        </td>
                                        <td>
                                            <span class="badge {{ $payroll->status_badge }}">{{ $payroll->status_label }}</span>
                                        </td>
                                        <td>
                                            <a href="{{ route('hr.payroll.show', $payroll->id) }}" class="btn btn-sm btn-info">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            @if($payroll->canBeApproved())
                                                <button class="btn btn-sm btn-success" onclick="approvePayroll({{ $payroll->id }})">
                                                    <i class="fas fa-check"></i>
                                                </button>
                                            @endif
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="text-center py-4">
                            <i class="fas fa-file-invoice-dollar fa-3x text-muted mb-3"></i>
                            <p class="text-muted">Aucun bulletin de paie pour cette période.</p>
                            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#calculatePayrollModal">
                                <i class="fas fa-calculator me-2"></i>Calculer maintenant
                            </button>
                        </div>
                    @endif
                </div>
            </div>
        </div>
        
        <div class="col-md-4">
            <div class="card">
                <div class="card-header bg-light">
                    <h5 class="mb-0">Statistiques par statut</h5>
                </div>
                <div class="card-body">
                    @php
                        $statusCounts = $summary['by_status'] ?? [];
                    @endphp
                    <div class="mb-3">
                        <div class="d-flex justify-content-between">
                            <span>Calculés</span>
                            <strong class="text-primary">{{ $statusCounts['calculated'] ?? 0 }}</strong>
                        </div>
                        <div class="progress mt-1">
                            <div class="progress-bar bg-primary" style="width: {{ $summary['total_employees'] > 0 ? (($statusCounts['calculated'] ?? 0) / $summary['total_employees']) * 100 : 0 }}%"></div>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <div class="d-flex justify-content-between">
                            <span>Approuvés</span>
                            <strong class="text-success">{{ $statusCounts['approved'] ?? 0 }}</strong>
                        </div>
                        <div class="progress mt-1">
                            <div class="progress-bar bg-success" style="width: {{ $summary['total_employees'] > 0 ? (($statusCounts['approved'] ?? 0) / $summary['total_employees']) * 100 : 0 }}%"></div>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <div class="d-flex justify-content-between">
                            <span>Payés</span>
                            <strong class="text-info">{{ $statusCounts['paid'] ?? 0 }}</strong>
                        </div>
                        <div class="progress mt-1">
                            <div class="progress-bar bg-info" style="width: {{ $summary['total_employees'] > 0 ? (($statusCounts['paid'] ?? 0) / $summary['total_employees']) * 100 : 0 }}%"></div>
                        </div>
                    </div>

                    <hr>
                    
                    <div class="text-center">
                        <h3 class="text-primary">{{ number_format($summary['avg_performance'] ?? 0, 1) }}%</h3>
                        <small>Performance moyenne</small>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Calculer Paie -->
<div class="modal fade" id="calculatePayrollModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Calculer les bulletins de paie</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('hr.payroll.calculate') }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Période</label>
                        <input type="month" name="period" class="form-control" value="{{ $currentPeriod }}" required>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Scope de calcul</label>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="scope" id="all" value="all" checked>
                            <label class="form-check-label" for="all">
                                Tous les employés
                            </label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="scope" id="department" value="department">
                            <label class="form-check-label" for="department">
                                Par département
                            </label>
                        </div>
                    </div>
                    
                    <div class="mb-3" id="departmentSelect" style="display: none;">
                        <label class="form-label">Département</label>
                        <select name="department_id" class="form-select">
                            <option value="">Sélectionner un département</option>
                            @foreach(\App\Models\Department::all() as $dept)
                                <option value="{{ $dept->id }}">{{ $dept->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-calculator me-2"></i>Calculer
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Approbation en lot -->
<div class="modal fade" id="bulkApproveModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Approbation en lot</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>Approuver tous les bulletins calculés pour la période {{ \Carbon\Carbon::createFromFormat('Y-m', $currentPeriod)->format('F Y') }} ?</p>
                <div class="alert alert-info">
                    <i class="fas fa-info-circle me-2"></i>
                    Cette action approuvera {{ $pendingApprovals }} bulletin(s) en attente.
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                <form method="POST" action="{{ route('hr.payroll.bulk-approve') }}" style="display: inline;">
                    @csrf
                    @foreach(\App\Models\PayrollRecord::calculated()->forPeriod($currentPeriod)->pluck('id') as $payrollId)
                        <input type="hidden" name="payroll_ids[]" value="{{ $payrollId }}">
                    @endforeach
                    <button type="submit" class="btn btn-success">
                        <i class="fas fa-check-double me-2"></i>Approuver tout
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
document.querySelectorAll('input[name="scope"]').forEach(radio => {
    radio.addEventListener('change', function() {
        const departmentSelect = document.getElementById('departmentSelect');
        if (this.value === 'department') {
            departmentSelect.style.display = 'block';
        } else {
            departmentSelect.style.display = 'none';
        }
    });
});

function approvePayroll(payrollId) {
    if (confirm('Approuver ce bulletin de paie ?')) {
        fetch(`/hr/payroll/${payrollId}/approve`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Content-Type': 'application/json'
            }
        }).then(response => {
            if (response.ok) {
                location.reload();
            } else {
                alert('Erreur lors de l\'approbation');
            }
        });
    }
}
</script>
@endsection