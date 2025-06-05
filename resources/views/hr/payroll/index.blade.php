@extends('hr.layouts.app')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1>Bulletins de Paie - {{ \Carbon\Carbon::createFromFormat('Y-m', $period)->format('F Y') }}</h1>
        <div>
            <a href="{{ route('hr.payroll.dashboard') }}" class="btn btn-secondary me-2">
                <i class="fas fa-arrow-left me-2"></i>Retour
            </a>
            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#calculatePayrollModal">
                <i class="fas fa-calculator me-2"></i>Calculer Paie
            </button>
        </div>
    </div>

    <!-- Filtres -->
    <div class="row mb-4">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header bg-light">
                    <h5 class="mb-0">Filtres</h5>
                </div>
                <div class="card-body">
                    <form method="GET" action="{{ route('hr.payroll.index') }}" class="row g-3">
                        <div class="col-md-3">
                            <label class="form-label">Période</label>
                            <select name="period" class="form-select" onchange="this.form.submit()">
                                @foreach($availablePeriods as $availablePeriod)
                                    <option value="{{ $availablePeriod }}" {{ $period == $availablePeriod ? 'selected' : '' }}>
                                        {{ \Carbon\Carbon::createFromFormat('Y-m', $availablePeriod)->format('F Y') }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Département</label>
                            <select name="department" class="form-select" onchange="this.form.submit()">
                                <option value="">Tous les départements</option>
                                @foreach($departments as $dept)
                                    <option value="{{ $dept->id }}" {{ $department == $dept->id ? 'selected' : '' }}>
                                        {{ $dept->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Statut</label>
                            <select name="status" class="form-select" onchange="this.form.submit()">
                                <option value="">Tous les statuts</option>
                                <option value="draft" {{ $status == 'draft' ? 'selected' : '' }}>Brouillon</option>
                                <option value="calculated" {{ $status == 'calculated' ? 'selected' : '' }}>Calculé</option>
                                <option value="approved" {{ $status == 'approved' ? 'selected' : '' }}>Approuvé</option>
                                <option value="paid" {{ $status == 'paid' ? 'selected' : '' }}>Payé</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">&nbsp;</label>
                            <div class="d-flex">
                                <a href="{{ route('hr.payroll.export', array_filter(['period' => $period, 'department' => $department, 'status' => $status])) }}" class="btn btn-outline-secondary">
                                    <i class="fas fa-download me-2"></i>Exporter
                                </a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Résumé de la période -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card bg-primary text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-white">Total Brut</h6>
                            <h3 class="text-white">{{ number_format($summary['total_gross'] ?? 0, 0) }} DH</h3>
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
                            <h6 class="text-white">Total Net</h6>
                            <h3 class="text-white">{{ number_format($summary['total_net'] ?? 0, 0) }} DH</h3>
                        </div>
                        <i class="fas fa-hand-holding-usd fa-2x opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-warning text-white">
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
            <div class="card bg-info text-white">
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
    @if($payrolls->where('status', 'calculated')->count() > 0)
    <div class="alert alert-info">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <i class="fas fa-info-circle me-2"></i>
                {{ $payrolls->where('status', 'calculated')->count() }} bulletin(s) calculé(s) en attente d'approbation
            </div>
            <button type="button" class="btn btn-sm btn-success" data-bs-toggle="modal" data-bs-target="#bulkApproveModal">
                <i class="fas fa-check-double me-2"></i>Approuver en lot
            </button>
        </div>
    </div>
    @endif

    <!-- Liste des bulletins -->
    <div class="card">
        <div class="card-header bg-light d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Bulletins de Paie</h5>
            <div class="form-check">
                <input class="form-check-input" type="checkbox" id="selectAll" onchange="toggleAll(this)">
                <label class="form-check-label" for="selectAll">
                    Tout sélectionner
                </label>
            </div>
        </div>
        <div class="card-body">
            @if($payrolls->count() > 0)
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th width="50">
                                    <input type="checkbox" id="masterCheckbox" onchange="toggleAll(this)">
                                </th>
                                <th>Employé</th>
                                <th>Département</th>
                                <th>Salaire Net</th>
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
                                    <input type="checkbox" class="payroll-checkbox" value="{{ $payroll->id }}" {{ $payroll->status == 'calculated' ? '' : 'disabled' }}>
                                </td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="avatar-sm me-3">
                                            <div class="avatar-title bg-success rounded-circle">
                                                {{ strtoupper(substr($payroll->user->name, 0, 1)) }}
                                            </div>
                                        </div>
                                        <strong>{{ $payroll->user->name }}</strong>
                                    </div>
                                </td>
                                <td>{{ $payroll->department->name }}</td>
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
                                    <span class="badge {{ $payroll->status_badge }}">{{ $payroll->status_label }}</span>
                                    @if($payroll->approved_at)
                                        <br><small class="text-muted">{{ $payroll->approved_at->format('d/m/Y') }}</small>
                                    @endif
                                </td>
                                <td>
                                    <div class="btn-group" role="group">
                                        <a href="{{ route('hr.payroll.show', $payroll->id) }}" class="btn btn-sm btn-info" title="Voir détails">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        
                                        @if($payroll->canBeApproved())
                                            <button class="btn btn-sm btn-success" onclick="approvePayroll({{ $payroll->id }})" title="Approuver">
                                                <i class="fas fa-check"></i>
                                            </button>
                                        @endif
                                        
                                        @if($payroll->canBePaid())
                                            <button class="btn btn-sm btn-primary" onclick="markAsPaid({{ $payroll->id }})" title="Marquer comme payé">
                                                <i class="fas fa-money-bill-wave"></i>
                                            </button>
                                        @endif
                                    </div>
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
                        <input type="month" name="period" class="form-control" value="{{ $period }}" required>
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
                            @foreach($departments as $dept)
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
                <p>Approuver les bulletins sélectionnés ?</p>
                <div class="alert alert-info">
                    <i class="fas fa-info-circle me-2"></i>
                    <span id="selectedCount">0</span> bulletin(s) sélectionné(s).
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                <button type="button" class="btn btn-success" onclick="bulkApprove()">
                    <i class="fas fa-check-double me-2"></i>Approuver la sélection
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Modal Marquer comme payé -->
<div class="modal fade" id="markAsPaidModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Marquer comme payé</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="markAsPaidForm" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Référence de paiement (optionnel)</label>
                        <input type="text" name="payment_reference" class="form-control" placeholder="Ex: VIR123456">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-money-bill-wave me-2"></i>Confirmer le paiement
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
// Gestion du scope de calcul
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

// Sélection multiple
function toggleAll(source) {
    const checkboxes = document.querySelectorAll('.payroll-checkbox:not(:disabled)');
    checkboxes.forEach(checkbox => {
        checkbox.checked = source.checked;
    });
    updateSelectedCount();
}

function updateSelectedCount() {
    const selected = document.querySelectorAll('.payroll-checkbox:checked').length;
    document.getElementById('selectedCount').textContent = selected;
}

// Écouter les changements de sélection
document.addEventListener('change', function(e) {
    if (e.target.classList.contains('payroll-checkbox')) {
        updateSelectedCount();
    }
});

// Approbation rapide
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

// Marquer comme payé
function markAsPaid(payrollId) {
    document.getElementById('markAsPaidForm').action = `/hr/payroll/${payrollId}/paid`;
    new bootstrap.Modal(document.getElementById('markAsPaidModal')).show();
}

// Approbation en lot
function bulkApprove() {
    const selected = Array.from(document.querySelectorAll('.payroll-checkbox:checked')).map(cb => cb.value);
    
    if (selected.length === 0) {
        alert('Aucun bulletin sélectionné');
        return;
    }

    if (confirm(`Approuver ${selected.length} bulletin(s) de paie ?`)) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = '/hr/payroll/bulk-approve';
        
        const csrfToken = document.createElement('input');
        csrfToken.type = 'hidden';
        csrfToken.name = '_token';
        csrfToken.value = document.querySelector('meta[name="csrf-token"]').content;
        form.appendChild(csrfToken);
        
        selected.forEach(id => {
            const input = document.createElement('input');
            input.type = 'hidden';
            input.name = 'payroll_ids[]';
            input.value = id;
            form.appendChild(input);
        });
        
        document.body.appendChild(form);
        form.submit();
    }
}
</script>

<style>
.avatar-sm {
    width: 32px;
    height: 32px;
}

.avatar-title {
    width: 100%;
    height: 100%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: bold;
    font-size: 14px;
}
</style>
@endsection