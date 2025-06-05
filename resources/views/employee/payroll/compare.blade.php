@extends('employee.layouts.app')

@section('content')
<div class="container-fluid">
    <h1>Comparaison des Bulletins de Paie</h1>
    
    <div class="card">
        <div class="card-body">
            <canvas id="comparisonChart" height="100"></canvas>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
const ctx = document.getElementById('comparisonChart').getContext('2d');
new Chart(ctx, {
    type: 'line',
    data: {
        labels: {!! json_encode($chartData['periods']) !!},
        datasets: [{
            label: 'Salaire Net',
            data: {!! json_encode($chartData['net_salary']) !!},
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
                        return value.toLocaleString() + ' €';
                    }
                }
            }
        }
    }
});
</script>
@endsection