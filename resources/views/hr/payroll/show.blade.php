@extends('hr.layouts.app')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1>Bulletin de Paie - {{ $payroll->user->name }}</h1>
        <div>
            <a href="{{ route('hr.payroll.index') }}" class="btn btn-secondary me-2">
                <i class="fas fa-arrow-left me-2"></i>Retour
            </a>
            @if($payroll->canBeApproved())
                <a href="{{ route('hr.payroll.approve', $payroll->id) }}" class="btn btn-success me-2" onclick="return confirm('Approuver ce bulletin ?')">
                    <i class="fas fa-check me-2"></i>Approuver
                </a>
            @endif
            <button type="button" class="btn btn-info" onclick="window.print()">
                <i class="fas fa-print me-2"></i>Imprimer
            </button>
        </div>
    </div>

    <!-- Détails complets du bulletin -->
    <div class="card">
        <div class="card-header bg-dark text-white">
            <h5 class="mb-0">{{ $payroll->formatted_period }} - {{ $payroll->user->name }}</h5>
        </div>
        <div class="card-body">
            <!-- Contenu identique à la vue employé mais avec actions admin -->
            <div class="table-responsive">
                <table class="table">
                    <tbody>
                        <tr>
                            <td><strong>Salaire de base</strong></td>
                            <td class="text-end">{{ number_format($payroll->base_salary, 2) }} €</td>
                        </tr>
                        <!-- Autres lignes de calcul... -->
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection