@extends('layouts.app')

@section('content')
<nav class="navbar navbar-expand-lg border-bottom" style="border-color: rgba(255,255,255,0.1) !important; background-color: rgba(0,0,0,0.1);">
  <div class="container">
    <a class="navbar-brand text-primary fw-bold" href="#">Minhas Finanças</a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
      <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="navbarNav">
      <ul class="navbar-nav ms-auto">
        <li class="nav-item">
            <span class="nav-link">Olá, {{ Auth::user()->name }}</span>
        </li>
        <li class="nav-item ps-3">
            <form action="{{ route('logout') }}" method="POST">
                @csrf
                <button type="submit" class="btn btn-sm btn-outline-danger mt-1">Sair</button>
            </form>
        </li>
      </ul>
    </div>
  </div>
</nav>

<div class="container mt-5">
    <div class="row">
        <!-- Sidebar Esquerda -->
        <div class="col-md-3">
            <div class="card auth-card mb-4 border-0">
                <div class="card-body">
                    <h6 class="text-uppercase text-muted fw-bold small mb-3">Financeiro</h6>
                    <div class="d-grid gap-2">
                        <a href="{{ route('dashboard') }}" class="btn btn-outline-light text-start"><i class="bi bi-graph-up me-2"></i> Painel Geral</a>
                        <button class="btn btn-primary text-start" data-bs-toggle="modal" data-bs-target="#importModal"><i class="bi bi-bank me-2"></i> Conciliação Bancária</button>
                        <a href="{{ route('transactions.index') }}" class="btn btn-outline-light text-start"><i class="bi bi-wallet2 me-2"></i> Transações</a>
                        <a href="{{ route('categories.index') }}" class="btn btn-outline-light text-start"><i class="bi bi-tags me-2"></i> Categorias</a>
                    </div>
                </div>
            </div>
            
            @if(count($imports) > 0)
            <div class="card auth-card border-0">
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
            <h2 class="fw-bold mb-4">Dashboard</h2>
            <div class="row g-4 mb-5">
                <div class="col-md-4">
                    <div class="card auth-card text-center p-4">
                        <h5 class="text-muted text-uppercase small fw-bold">Saldo Atual</h5>
                        <h3 class="fw-bold {{ $balance < 0 ? 'text-danger' : 'text-primary' }} mb-0">R$ {{ number_format($balance, 2, ',', '.') }}</h3>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card auth-card text-center p-4">
                        <h5 class="text-muted text-uppercase small fw-bold">Receitas Mês</h5>
                        <h3 class="fw-bold text-success mb-0">R$ {{ number_format($incomes, 2, ',', '.') }}</h3>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card auth-card text-center p-4">
                        <h5 class="text-muted text-uppercase small fw-bold">Despesas Mês</h5>
                        <h3 class="fw-bold text-danger mb-0">R$ {{ number_format($expenses, 2, ',', '.') }}</h3>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Conciliação -->
<div class="modal fade" id="importModal" tabindex="-1" aria-labelledby="importModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content auth-card">
      <div class="modal-header border-bottom-0">
        <h1 class="modal-title fs-5 fw-bold" id="importModalLabel">Importar OFX/CSV</h1>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <form action="{{ route('bank.import') }}" method="POST" enctype="multipart/form-data" id="importForm">
          @csrf
          <div class="modal-body">
             <div class="mb-3">
                 <label class="form-label text-muted small text-uppercase">Extrato Bancário</label>
                 <input class="form-control" type="file" name="ofx_file" required accept=".ofx,.csv">
                 <div class="form-text text-white-50 small mt-2">
                     O arquivo será processado em segundo plano e você será notificado em tempo real assim que as transações forem identificadas.
                 </div>
             </div>
          </div>
          <div class="modal-footer border-top-0">
            <button type="button" class="btn btn-outline-light" data-bs-dismiss="modal">Cancelar</button>
            <button type="submit" id="btnImport" class="btn btn-primary">Iniciar Processamento</button>
          </div>
      </form>
    </div>
  </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const importForm = document.getElementById('importForm');
        const btnImport = document.getElementById('btnImport');
        
        importForm.addEventListener('submit', function() {
            btnImport.disabled = true;
            btnImport.innerHTML = '<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span> Enviando Fila...';
        });
    });
</script>
@endsection
