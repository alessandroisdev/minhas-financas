@extends('layouts.app')

@section('content')
@include('layouts.navbar')

<div class="container">
    <div class="row">
        <!-- Sidebar Esquerda -->
        <div class="col-md-3">
            @include('layouts.sidebar')
            
            @if(count($imports) > 0)
            <div class="card bg-dark border-0 shadow-sm" style="background-color: rgba(0,0,0,0.2) !important;">
                <div class="card-body">
                    <h6 class="text-uppercase text-muted fw-bold small mb-3">Últimas Conciliações</h6>
                    <ul class="list-unstyled">
                        @foreach($imports as $imp)
                            <li class="mb-2">
                                <span class="d-block small fw-bold">{{ $imp->filename }}</span>
                                <span class="badge {{ $imp->status == 'completed' ? 'text-bg-success' : ($imp->status == 'failed' ? 'text-bg-danger' : 'text-bg-primary') }} small">
                                    {{ ucfirst($imp->status) }}
                                </span>
                            </li>
                        @endforeach
                    </ul>
                </div>
            </div>
            @endif
        </div>

        <!-- Conteúdo Principal -->
        <div class="col-md-9">
            <h2 class="fw-bold mb-4">Dashboard Geral</h2>
            <div class="row g-4 mb-5">
                <div class="col-md-4">
                    <div class="card bg-dark border-0 text-center p-4 shadow-sm" style="background-color: rgba(0,0,0,0.2) !important;">
                        <h5 class="text-muted text-uppercase small fw-bold">Saldo Atual</h5>
                        <h3 class="fw-bold {{ $balance < 0 ? 'text-danger' : 'text-primary' }} mb-0">R$ {{ number_format($balance, 2, ',', '.') }}</h3>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card bg-dark border-0 text-center p-4 shadow-sm" style="background-color: rgba(0,0,0,0.2) !important;">
                        <h5 class="text-muted text-uppercase small fw-bold">Receitas Mês</h5>
                        <h3 class="fw-bold text-success mb-0">R$ {{ number_format($incomes, 2, ',', '.') }}</h3>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card bg-dark border-0 text-center p-4 shadow-sm" style="background-color: rgba(0,0,0,0.2) !important;">
                        <h5 class="text-muted text-uppercase small fw-bold">Despesas Mês</h5>
                        <h3 class="fw-bold text-danger mb-0">R$ {{ number_format($expenses, 2, ',', '.') }}</h3>
                    </div>
                </div>
            </div>
            </div>

            <!-- Gráficos -->
            <div class="row g-4 mb-4">
                <div class="col-md-8">
                    <div class="card bg-dark border-0 shadow-sm p-4" style="background-color: rgba(0,0,0,0.2) !important;">
                        <h5 class="text-muted text-uppercase small fw-bold mb-4">Evolução do Fluxo de Caixa (6 Meses)</h5>
                        <canvas id="cashflowChart" height="100"></canvas>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card bg-dark border-0 shadow-sm p-4" style="background-color: rgba(0,0,0,0.2) !important;">
                        <h5 class="text-muted text-uppercase small fw-bold mb-4">Despesas por Categoria (Mês Atual)</h5>
                        <div class="position-relative d-flex justify-content-center align-items-center" style="height: 250px;">
                            <canvas id="categoryChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        // Configurações globais Chart.js para o layout Dark
        Chart.defaults.color = 'rgba(255, 255, 255, 0.5)';
        Chart.defaults.borderColor = 'rgba(255, 255, 255, 0.05)';
        Chart.defaults.font.family = 'Outfit';

        // 1. Fluxo de Caixa Histórico (Bar Chart)
        const flowData = @json($flowData);
        const monthsMap = {};
        
        // Garante a ordem exata dos ultimos 6 meses no eixo X
        const baseDate = new Date();
        baseDate.setDate(1); // Evita bug de pulo de mes no dia 31
        for(let i=5; i>=0; i--) {
            let d = new Date(baseDate.getFullYear(), baseDate.getMonth() - i, 1);
            let mY = d.getFullYear() + "-" + String(d.getMonth()+1).padStart(2, '0');
            monthsMap[mY] = { income: 0, expense: 0 };
        }

        flowData.forEach(item => {
            if(monthsMap[item.month_year]) {
                monthsMap[item.month_year][item.type] = parseFloat(item.total);
            }
        });

        const labels = Object.keys(monthsMap).map(m => {
            let parts = m.split('-');
            return parts[1] + '/' + parts[0];
        });
        const incomeData = Object.values(monthsMap).map(v => v.income);
        const expenseData = Object.values(monthsMap).map(v => v.expense);

        const ctxFlow = document.getElementById('cashflowChart').getContext('2d');
        new Chart(ctxFlow, {
            type: 'bar',
            data: {
                labels: labels,
                datasets: [
                    {
                        label: 'Receitas (+)',
                        data: incomeData,
                        backgroundColor: '#198754',
                        borderRadius: 4
                    },
                    {
                        label: 'Despesas (-)',
                        data: expenseData,
                        backgroundColor: '#dc3545',
                        borderRadius: 4
                    }
                ]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: { position: 'top', labels: { usePointStyle: true } },
                    tooltip: { mode: 'index', intersect: false }
                },
                scales: {
                    y: { beginAtZero: true, grid: { drawBorder: false } },
                    x: { grid: { display: false } }
                }
            }
        });

        // 2. Gráfico Rosca Categorias Mês (Doughnut)
        const catData = @json($expensesByCategory);
        const ctxCat = document.getElementById('categoryChart').getContext('2d');
        
        if (catData.length === 0) {
            new Chart(ctxCat, {
                type: 'doughnut',
                data: {
                    labels: ['Sem Despesas'],
                    datasets: [{ data: [1], backgroundColor: ['rgba(255,255,255,0.05)'], borderWidth: 0 }]
                },
                options: { plugins: { tooltip: { enabled: false } }, cutout: '75%' }
            });
        } else {
            const catLabels = catData.map(item => item.category ? item.category.name : 'Sem Categoria');
            const catValues = catData.map(item => parseFloat(item.total));
            const catColors = catData.map(item => item.category ? item.category.color : '#6c757d');

            new Chart(ctxCat, {
                type: 'doughnut',
                data: {
                    labels: catLabels,
                    datasets: [{
                        data: catValues,
                        backgroundColor: catColors,
                        borderWidth: 0,
                        hoverOffset: 6
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { position: 'bottom', labels: { usePointStyle: true, padding: 20 } }
                    },
                    cutout: '75%'
                }
            });
        }
    });
</script>
@endsection
