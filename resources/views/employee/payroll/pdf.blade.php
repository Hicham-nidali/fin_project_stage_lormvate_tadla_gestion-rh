<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bulletin de Paie - {{ $payroll->user->name }} - {{ $payroll->formatted_period }}</title>
    <style>
        body {
            font-family: 'Arial', sans-serif;
            font-size: 14px;
            line-height: 1.5;
            color: #333;
            margin: 0;
            padding: 20px;
            background: #fff;
        }
        
        .container {
            max-width: 800px;
            margin: 0 auto;
            background: white;
            padding: 30px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        
        .header {
            text-align: center;
            border-bottom: 3px solid #333;
            padding-bottom: 20px;
            margin-bottom: 30px;
        }
        
        .header h1 {
            font-size: 24px;
            margin: 0;
            color: #2c3e50;
        }
        
        .header h2 {
            font-size: 18px;
            margin: 10px 0 0 0;
            color: #7f8c8d;
        }
        
        .company-section {
            margin-bottom: 25px;
            padding: 15px;
            background: #f8f9fa;
            border-left: 4px solid #3498db;
        }
        
        .employee-section {
            background: #e8f5e8;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 25px;
            border: 1px solid #27ae60;
        }
        
        .info-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
            margin-bottom: 20px;
        }
        
        .info-item {
            margin-bottom: 8px;
        }
        
        .info-label {
            font-weight: bold;
            color: #2c3e50;
            display: inline-block;
            min-width: 120px;
        }
        
        .status-badge {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 15px;
            font-size: 12px;
            font-weight: bold;
            text-transform: uppercase;
        }
        
        .status-approved {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        
        .salary-table {
            width: 100%;
            border-collapse: collapse;
            margin: 25px 0;
            font-size: 14px;
            background: white;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        
        .salary-table th {
            background: #34495e;
            color: white;
            padding: 15px 12px;
            text-align: left;
            font-weight: bold;
            text-transform: uppercase;
            font-size: 12px;
            letter-spacing: 0.5px;
        }
        
        .salary-table td {
            padding: 12px;
            border-bottom: 1px solid #ecf0f1;
            vertical-align: middle;
        }
        
        .salary-table tr:hover {
            background: #f8f9fa;
        }
        
        .amount-column {
            text-align: right;
            font-weight: bold;
            font-family: 'Courier New', monospace;
        }
        
        .positive-amount {
            color: #27ae60;
        }
        
        .negative-amount {
            color: #e74c3c;
        }
        
        .total-row {
            background: #ecf0f1 !important;
            font-weight: bold;
            border-top: 2px solid #bdc3c7;
        }
        
        .final-row {
            background: #27ae60 !important;
            color: white !important;
            font-size: 16px;
            font-weight: bold;
        }
        
        .final-row td {
            border-bottom: none;
            padding: 15px 12px;
        }
        
        .performance-section {
            background: #fff3cd;
            border: 1px solid #ffeaa7;
            border-radius: 8px;
            padding: 20px;
            margin: 25px 0;
        }
        
        .performance-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 20px;
            text-align: center;
            margin-bottom: 15px;
        }
        
        .performance-item {
            padding: 15px;
            background: white;
            border-radius: 6px;
            border: 1px solid #ddd;
        }
        
        .performance-score {
            font-size: 24px;
            font-weight: bold;
            color: #2980b9;
            display: block;
        }
        
        .performance-label {
            font-size: 12px;
            color: #7f8c8d;
            margin-top: 5px;
        }
        
        .criteria-list {
            margin-top: 15px;
            padding-left: 0;
        }
        
        .criteria-item {
            list-style: none;
            padding: 8px 0;
            border-bottom: 1px solid #eee;
        }
        
        .criteria-item:before {
            content: "✓";
            color: #27ae60;
            font-weight: bold;
            margin-right: 10px;
        }
        
        .footer {
            margin-top: 40px;
            padding-top: 20px;
            border-top: 2px solid #ecf0f1;
            text-align: center;
            color: #7f8c8d;
            font-size: 12px;
        }
        
        .footer-note {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 6px;
            margin: 15px 0;
            font-style: italic;
        }
        
        .signature-section {
            margin-top: 40px;
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 40px;
        }
        
        .signature-box {
            text-align: center;
            padding: 20px 0;
            border-top: 1px solid #bdc3c7;
        }
        
        .net-salary-highlight {
            font-size: 28px !important;
            text-shadow: 1px 1px 2px rgba(0,0,0,0.1);
        }
        
        @media print {
            body {
                margin: 0;
                padding: 10px;
                font-size: 12px;
            }
            .container {
                box-shadow: none;
                padding: 20px;
            }
            .info-grid {
                display: block;
            }
            .performance-grid {
                display: block;
            }
            .signature-section {
                display: block;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- En-tête -->
        <div class="header">
            <h1>BULLETIN DE PAIE</h1>
            <h2>{{ $payroll->formatted_period }}</h2>
        </div>

        <!-- Informations entreprise -->
        <div class="company-section">
            <h3 style="margin: 0 0 10px 0; color: #2c3e50;">🏢 TechCorp</h3>
            <div>123 Avenue Mohammed V<br>
            20000 Casablanca, Maroc<br>
            📞 +212 5 22 xx xx xx | ✉️ contact@techcorp.ma</div>
        </div>

        <!-- Informations employé et période -->
        <div class="employee-section">
            <div class="info-grid">
                <div>
                    <div class="info-item">
                        <span class="info-label">👤 Employé:</span> 
                        <strong>{{ $payroll->user->name }}</strong>
                    </div>
                    <div class="info-item">
                        <span class="info-label">🏢 Département:</span> 
                        {{ $payroll->department->name }}
                    </div>
                    <div class="info-item">
                        <span class="info-label">📅 Période:</span> 
                        {{ $payroll->period_start->format('d/m/Y') }} - {{ $payroll->period_end->format('d/m/Y') }}
                    </div>
                </div>
                <div>
                    <div class="info-item">
                        <span class="info-label">⚙️ Calculé le:</span> 
                        {{ $payroll->calculated_at ? $payroll->calculated_at->format('d/m/Y à H:i') : 'N/A' }}
                    </div>
                    <div class="info-item">
                        <span class="info-label">📊 Statut:</span> 
                        <span class="status-badge status-approved">{{ $payroll->status_label }}</span>
                    </div>
                    @if($payroll->approved_at)
                    <div class="info-item">
                        <span class="info-label">✅ Approuvé le:</span> 
                        {{ $payroll->approved_at->format('d/m/Y à H:i') }}
                    </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Tableau détaillé des calculs -->
        <table class="salary-table">
            <thead>
                <tr>
                    <th>📋 Description</th>
                    <th>ℹ️ Détails</th>
                    <th style="text-align: right;">💰 Montant (€)</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td><strong>💼 Salaire de base</strong></td>
                    <td>Salaire mensuel contractuel</td>
                    <td class="amount-column">{{ number_format($payroll->base_salary, 2) }}</td>
                </tr>

                @if($payroll->hasAdjustment())
                <tr>
                    <td><strong>📈 Ajustement de performance</strong></td>
                    <td>{{ $payroll->formatted_adjustment }} basé sur l'évaluation de performance</td>
                    <td class="amount-column {{ $payroll->isPositiveAdjustment() ? 'positive-amount' : 'negative-amount' }}">
                        {{ $payroll->isPositiveAdjustment() ? '+' : '' }}{{ number_format($payroll->adjustment_amount, 2) }}
                    </td>
                </tr>
                @endif

                @if($payroll->hasOvertime())
                <tr>
                    <td><strong>⏰ Heures supplémentaires</strong></td>
                    <td>{{ $payroll->overtime_hours }}h à {{ number_format($payroll->overtime_rate, 2) }} €/h (taux majoré +50%)</td>
                    <td class="amount-column positive-amount">+{{ number_format($payroll->overtime_amount, 2) }}</td>
                </tr>
                @endif

                <tr class="total-row">
                    <td><strong>💰 SALAIRE BRUT TOTAL</strong></td>
                    <td><em>Total avant déductions</em></td>
                    <td class="amount-column"><strong>{{ number_format($payroll->gross_salary, 2) }}</strong></td>
                </tr>

                <tr>
                    <td><strong>📉 Déductions obligatoires</strong></td>
                    <td>
                        Charges sociales et impôts 
                        <small>({{ number_format(($payroll->deductions / $payroll->gross_salary) * 100, 1) }}% du brut)</small>
                        <br><small style="color: #7f8c8d;">• Sécurité sociale • Retraite • Assurance maladie • IR</small>
                    </td>
                    <td class="amount-column negative-amount">-{{ number_format($payroll->deductions, 2) }}</td>
                </tr>

                <tr class="final-row">
                    <td><strong>💳 SALAIRE NET À PAYER</strong></td>
                    <td><strong>Montant à virer sur votre compte</strong></td>
                    <td class="amount-column net-salary-highlight">{{ number_format($payroll->net_salary, 2) }} €</td>
                </tr>
            </tbody>
        </table>

        @if($payroll->performance_data)
        <!-- Section analyse de performance -->
        <div class="performance-section">
            <h3 style="margin: 0 0 15px 0; color: #2c3e50;">📊 Analyse de Performance</h3>
            
            <div class="performance-grid">
                <div class="performance-item">
                    <span class="performance-score">{{ $payroll->performance_data['overall_score'] ?? 0 }}%</span>
                    <div class="performance-label">Score Global</div>
                </div>
                <div class="performance-item">
                    <span class="performance-score">{{ $payroll->performance_data['attendance_rate'] ?? 0 }}%</span>
                    <div class="performance-label">Taux de Présence</div>
                </div>
                <div class="performance-item">
                    <span class="performance-score">{{ $payroll->performance_data['task_completion_rate'] ?? 0 }}%</span>
                    <div class="performance-label">Tâches Complétées</div>
                </div>
            </div>

            @if($payroll->adjustment_details && count($payroll->adjustment_details) > 0)
            <div>
                <strong>🎯 Critères d'ajustement appliqués:</strong>
                <ul class="criteria-list">
                    @foreach($payroll->adjustment_details as $detail)
                    <li class="criteria-item">{{ $detail }}</li>
                    @endforeach
                </ul>
            </div>
            @endif
        </div>
        @endif

        <!-- Signatures -->
        <div class="signature-section">
            <div class="signature-box">
                <strong>Signature de l'Employé</strong><br>
                <small>{{ $payroll->user->name }}</small>
            </div>
            <div class="signature-box">
                <strong>Service des Ressources Humaines</strong><br>
                <small>TechCorp</small>
            </div>
        </div>

        <!-- Pied de page -->
        <div class="footer">
            <div class="footer-note">
                <strong>📝 Note importante:</strong> Ce bulletin de paie est généré automatiquement par le système de gestion RH TechCorp. 
                Il constitue un justificatif officiel de rémunération. Conservez-le précieusement pour vos démarches administratives.
            </div>
            
            <div style="margin-top: 20px;">
                <strong>Document généré le {{ now()->format('d/m/Y à H:i') }}</strong><br>
                <em>TechCorp - Département des Ressources Humaines</em><br>
                <small>Système de Gestion RH v2.0 • Confidentiel</small>
            </div>
        </div>
    </div>
</body>
</html>