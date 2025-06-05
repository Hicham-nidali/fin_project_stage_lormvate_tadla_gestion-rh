@extends('employee.layouts.app')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1>Bulletin de Paie - {{ $payroll->formatted_period }}</h1>
        <div>
            <a href="{{ route('employee.payroll.index') }}" class="btn btn-secondary me-2">
                <i class="fas fa-arrow-left me-2"></i>Retour
            </a>
            <a href="{{ route('employee.payroll.download', $payroll->id) }}" class="btn btn-primary">
                <i class="fas fa-download me-2"></i>Télécharger PDF
            </a>
        </div>
    </div>

    <!-- En-tête du bulletin -->
    <div class="card mb-4">
        <div class="card-header bg-success text-white">
            <div class="row">
                <div class="col-md-8">
                    <h5 class="mb-1 text-white">{{ $payroll->user->name }}</h5>
                    <small class="text-white">Département: {{ $payroll->department->name }}</small>
                </div>
                <div class="col-md-4 text-end">
                    <h3 class="mb-0 text-white">{{ number_format($payroll->net_salary, 2) }} €</h3>
                    <small class="text-white">Salaire net</small>
                </div>
            </div>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-4">
                    <strong>Période:</strong><br>
                    {{ $payroll->period_start->format('d/m/Y') }} - {{ $payroll->period_end->format('d/m/Y') }}
                </div>
                <div class="col-md-4">
                    <strong>Calculé le:</strong><br>
                    {{ $payroll->calculated_at ? $payroll->calculated_at->format('d/m/Y H:i') : 'N/A' }}
                </div>
                <div class="col-md-4">
                    <strong>Statut:</strong><br>
                    <span class="badge {{ $payroll->status_badge }} fs-6">{{ $payroll->status_label }}</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Détail des calculs -->
    <div class="card mb-4">
        <div class="card-header bg-light">
            <h5 class="mb-0">Détail des Calculs</h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table">
                    <tbody>
                        <tr>
                            <td><strong>Salaire de base</strong></td>
                            <td class="text-end">{{ number_format($payroll->base_salary, 2) }} €</td>
                        </tr>
                        
                        @if($payroll->hasAdjustment())
                        <tr class="{{ $payroll->isPositiveAdjustment() ? 'table-success' : 'table-warning' }}">
                            <td>
                                <strong>Ajustement de performance ({{ $payroll->formatted_adjustment }})</strong>
                                <br><small class="text-muted">Basé sur l'évaluation de performance</small>
                            </td>
                            <td class="text-end">
                                {{ $payroll->isPositiveAdjustment() ? '+' : '' }}{{ number_format($payroll->adjustment_amount, 2) }} €
                            </td>
                        </tr>
                        @endif
                        
                        @if($payroll->hasOvertime())
                        <tr class="table-info">
                            <td>
                                <strong>Heures supplémentaires</strong>
                                <br><small class="text-muted">{{ $payroll->overtime_hours }}h à {{ number_format($payroll->overtime_rate, 2) }} €/h</small>
                            </td>
                            <td class="text-end">+{{ number_format($payroll->overtime_amount, 2) }} €</td>
                        </tr>
                        @endif
                        
                        <tr class="table-primary">
                            <td><strong>Salaire brut total</strong></td>
                            <td class="text-end"><strong>{{ number_format($payroll->gross_salary, 2) }} €</strong></td>
                        </tr>
                        
                        <tr class="table-danger">
                            <td>
                                <strong>Déductions (charges sociales, impôts)</strong>
                                <br><small class="text-muted">{{ number_format(($payroll->deductions / $payroll->gross_salary) * 100, 1) }}% du salaire brut</small>
                            </td>
                            <td class="text-end">-{{ number_format($payroll->deductions, 2) }} €</td>
                        </tr>
                        
                        <tr class="table-success">
                            <td><strong>SALAIRE NET À PAYER</strong></td>
                            <td class="text-end"><strong class="fs-5">{{ number_format($payroll->net_salary, 2) }} €</strong></td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    @if($payroll->performance_data)
    <!-- Analyse de performance -->
    <div class="card">
        <div class="card-header bg-light">
            <h5 class="mb-0">Mon Analyse de Performance</h5>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-4">
                    <div class="text-center">
                        <h4 class="text-primary">{{ $payroll->performance_data['overall_score'] ?? 0 }}%</h4>
                        <small class="text-muted">Score Global</small>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="text-center">
                        <h4 class="text-success">{{ $payroll->performance_data['attendance_rate'] ?? 0 }}%</h4>
                        <small class="text-muted">Taux de Présence</small>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="text-center">
                        <h4 class="text-info">{{ $payroll->performance_data['task_completion_rate'] ?? 0 }}%</h4>
                        <small class="text-muted">Tâches Complétées</small>
                    </div>
                </div>
            </div>

            @if($payroll->adjustment_details)
            <hr>
            <h6>Critères d'ajustement:</h6>
            <ul class="list-unstyled">
                @foreach($payroll->adjustment_details as $detail)
                <li><i class="fas fa-check-circle text-success me-2"></i>{{ $detail }}</li>
                @endforeach
            </ul>
            @endif
        </div>
    </div>
    @endif
</div>
@endsection