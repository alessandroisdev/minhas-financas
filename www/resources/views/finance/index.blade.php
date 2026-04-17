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
    </div>
</div>
@endsection
