<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bulletin de Paie - {{ $payroll->formatted_period }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 14px;
            line-height: 1.6;
            color: #333;
            margin: 0;
            padding: 20px;
        }
        .header {
            text-align: center;
            border-bottom: 2px solid #333;
            padding-bottom: 20px;
            margin-bottom: 30px;
        }
        .company-info {
            text-align: left;
            margin-bottom: 20px;
        }
        .employee-info {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
        }
        .salary-details {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        .salary-details th,
        .salary-details td {
            padding: 10px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }
        .salary-details th {
            background-color: #f8f9fa;
            font-weight: bold;
        }
        .salary-details .amount {
            text-align: right;
        }
        .total-row {
            background-color: #e9ecef;
            font-weight: bold;
        }
        .final-row {
            background-color: #28a745;
            color: white;
            font-weight: bold;
            font-size: 16px;
        }
        .performance-section {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 5px;
            margin-top: 20px;
        }
        .footer {
            margin-top: 40px;
            padding-top: 20px;
            border-top: 1px solid #ddd;
            font-size: 12px;
            color: #666;
        }
        .positive {
            color: #28a745;
        }
        .negative {
            color: #dc3545;
        }
        @media print {
            body {
                margin: 0;
                padding: 15px;
            }
            .no-print {
                display: none;
            }
        }
    </style>
</head>
<body>
    <!-- En-tête -->
    <div class="header">
        <h1>BULLETIN DE PAIE</h1>
        <h2>{{ $payroll->formatted_period }}</h2>
    </div>

    <!-- Informations entreprise -->
    <div class="company-info">
        <strong>TechCorp</strong><br>
        123 Avenue Mohammed V<br>
        Casablanca, Maroc<br>
        Tél: +212 5 22 xx xx xx
    </div>

    <!-- Informations employé -->
    <div class="employee-info">
        <div style="display: flex; justify-content: space-between;">
            <div>
                <strong>Employé:</strong> {{ $payroll->user->name }}<br>
                <strong>Département:</strong> {{ $payroll->department->name }}<br>
                <strong>Période:</strong> {{ $payroll->period_start->format('d/m/Y') }} - {{ $payroll->period_end->format('d/m/Y') }}
            </div>
            <div>
                <strong>Calculé le:</strong> {{ $payroll->calculated_at ? $payroll->calculated_at->format('d/m/Y H:i') : 'N/A' }}<br>
                <strong>Statut:</strong> {{ $payroll->status_label }}<br>
                @if($payroll->approved_at)
                <strong>Approuvé le:</strong> {{ $payroll->approved_at->format('d/m/Y H:i') }}
                @endif
            </div>
        </div>
    </div>

    <!-- Détail des calculs -->
    <table class="salary-details">
        <thead>
            <tr>
                <th>Description</th>
                <th>Détails</th>
                <th class="amount">Montant (€)</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td><strong>Salaire de base</strong></td>
                <td>Salaire mensuel</td>
                <td class="amount">{{ number_format($payroll->base_salary, 2) }}</td>
            </tr>

            @if($payroll->hasAdjustment())
            <tr class="{{ $payroll->isPositiveAdjustment() ? 'positive' : 'negative' }}">
                <td><strong>Ajustement de performance</strong></td>
                <td>{{ $payroll->formatted_adjustment }} basé sur l'évaluation</td>
                <td class="amount">{{ $payroll->isPositiveAdjustment() ? '+' : '' }}{{ number_format($payroll->adjustment_amount, 2) }}</td>
            </tr>
            @endif

            @if($payroll->hasOvertime())
            <tr class="positive">
                <td><strong>Heures supplémentaires</strong></td>
                <td>{{ $payroll->overtime_hours }}h à {{ number_format($payroll->overtime_rate, 2) }} €/h</td>
                <td class="amount">+{{ number_format($payroll->overtime_amount, 2) }}</td>
            </tr>
            @endif

            <tr class="total-row">
                <td><strong>SALAIRE BRUT TOTAL</strong></td>
                <td></td>
                <td class="amount"><strong>{{ number_format($payroll->gross_salary, 2) }}</strong></td>
            </tr>

            <tr class="negative">
                <td><strong>Déductions (charges sociales, impôts)</strong></td>
                <td>{{ number_format(($payroll->deductions / $payroll->gross_salary) * 100, 1) }}% du salaire brut</td>
                <td class="amount">-{{ number_format($payroll->deductions, 2) }}</td>
            </tr>

            <tr class="final-row">
                <td><strong>SALAIRE NET À PAYER</strong></td>
                <td></td>
                <td class="amount"><strong>{{ number_format($payroll->net_salary, 2) }} €</strong></td>
            </tr>
        </tbody>
    </table>

    @if($payroll->performance_data)
    <!-- Section performance -->
    <div class="performance-section">
        <h3>Analyse de Performance</h3>
        <div style="display: flex; justify-content: space-around; text-align: center;">
            <div>
                <strong>{{ $payroll->performance_data['overall_score'] ?? 0 }}%</strong><br>
                <small>Score Global</small>
            </div>
            <div>
                <strong>{{ $payroll->performance_data['attendance_rate'] ?? 0 }}%</strong><br>
                <small>Taux de Présence</small>
            </div>
            <div>
                <strong>{{ $payroll->performance_data['task_completion_rate'] ?? 0 }}%</strong><br>
                <small>Tâches Complétées</small>
            </div>
        </div>

        @if($payroll->adjustment_details)
        <div style="margin-top: 15px;">
            <strong>Critères d'ajustement:</strong>
            <ul>
                @foreach($payroll->adjustment_details as $detail)
                <li>{{ $detail }}</li>
                @endforeach
            </ul>
        </div>
        @endif
    </div>
    @endif

    <!-- Pied de page -->
    <div class="footer">
        <p><strong>Note:</strong> Ce bulletin de paie est généré automatiquement par le système de gestion RH.</p>
        <p>Document généré le {{ now()->format('d/m/Y à H:i') }}</p>
        <p style="text-align: center; margin-top: 20px;">
            <strong>TechCorp - Département des Ressources Humaines</strong>
        </p>
    </div>
</body>
</html>