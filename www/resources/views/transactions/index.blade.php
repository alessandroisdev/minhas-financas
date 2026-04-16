@extends('layouts.app')

@section('content')
<nav class="navbar navbar-expand-lg border-bottom" style="border-color: rgba(255,255,255,0.1) !important; background-color: rgba(0,0,0,0.1);">
  <div class="container">
    <a class="navbar-brand text-primary fw-bold" href="{{ route('dashboard') }}">Minhas Finanças</a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
      <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="navbarNav">
      <ul class="navbar-nav ms-auto">
        <li class="nav-item">
            <span class="nav-link">Olá, {{ Auth::user()->name }}</span>
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
                        <a href="{{ route('transactions.index') }}" class="btn btn-primary text-start"><i class="bi bi-wallet2 me-2"></i> Transações</a>
                        <a href="{{ route('categories.index') }}" class="btn btn-outline-light text-start"><i class="bi bi-tags me-2"></i> Categorias</a>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-9">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2 class="fw-bold mb-0">Extrato ({{ $month }}/{{ $year }})</h2>
                <button class="btn btn-success fw-bold px-4" data-bs-toggle="modal" data-bs-target="#newTxModal">+ Nova Transação</button>
            </div>

            <div class="card auth-card border-0">
                <div class="card-body p-0">
                    <table class="table table-dark table-hover mb-0">
                        <thead class="small text-uppercase text-muted">
                            <tr>
                                <th class="ps-4">Data</th>
                                <th>Descrição / Categoria</th>
                                <th>Valor</th>
                                <th>Status</th>
                                <th class="text-end pe-4">Ação</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($transactions as $tx)
                            <tr class="align-middle">
                                <td class="ps-4 text-white-50 small">{{ $tx->date->format('d/m/Y') }}</td>
                                <td>
                                    <span class="fw-bold d-block">{{ $tx->description }}</span>
                                    <span class="badge" style="background-color: {{ optional($tx->category)->color ?? '#333' }}; opacity: 0.8;">
                                        {{ optional($tx->category)->name ?? 'Sem categoria' }}
                                    </span>
                                    @if($tx->recurrence_type)
                                        <span class="badge text-bg-secondary ms-1"><i class="bi bi-arrow-repeat"></i> {{ $tx->recurrence_type }}</span>
                                    @endif
                                </td>
                                <td>
                                    <h6 class="fw-bold mb-0 {{ $tx->type == 'income' ? 'text-success' : 'text-danger' }}">
                                        {{ $tx->type == 'income' ? '+' : '-' }} R$ {{ number_format($tx->amount, 2, ',', '.') }}
                                    </h6>
                                </td>
                                <td>
                                    @if($tx->status == 'pending')
                                        <span class="badge text-bg-warning">Pendente</span>
                                    @elseif($tx->status == 'paid')
                                        <span class="badge text-bg-success">Pago</span>
                                    @elseif($tx->status == 'reconciled')
                                        <span class="badge text-bg-primary">Conciliado</span>
                                    @endif
                                </td>
                                <td class="text-end pe-4">
                                    @if($tx->status == 'pending')
                                    <form action="{{ route('transactions.pay', $tx->id) }}" method="POST" class="d-inline">
                                        @csrf
                                        <button class="btn btn-sm btn-outline-success">Pagar</button>
                                    </form>
                                    @endif
                                    
                                    <form action="{{ route('transactions.destroy', $tx->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Excluir esta transação?')">
                                        @csrf
                                        @method('DELETE')
                                        <button class="btn btn-sm btn-outline-danger" title="Apagar"><i class="bi bi-trash"></i></button>
                                    </form>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="5" class="text-center text-muted fw-semibold py-4">
                                    Nenhuma transação encontrada no período.
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Nova Transação -->
<div class="modal fade" id="newTxModal" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content auth-card">
      <div class="modal-header border-bottom-0">
        <h5 class="modal-title fw-bold">Criar Transação</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
      </div>
      <form action="{{ route('transactions.store') }}" method="POST">
          @csrf
          <div class="modal-body">
              <div class="mb-3">
                  <label class="form-label text-muted small text-uppercase">Descrição</label>
                  <input type="text" name="description" class="form-control" required>
              </div>
              
              <div class="row">
                  <div class="col-md-6 mb-3">
                      <label class="form-label text-muted small text-uppercase">Valor (R$)</label>
                      <input type="number" step="0.01" name="amount" class="form-control" required placeholder="0.00">
                  </div>
                  <div class="col-md-6 mb-3">
                      <label class="form-label text-muted small text-uppercase">Data</label>
                      <input type="date" name="date" class="form-control" value="{{ date('Y-m-d') }}" required>
                  </div>
              </div>

              <div class="row">
                  <div class="col-md-6 mb-3">
                      <label class="form-label text-muted small text-uppercase">Tipo</label>
                      <select name="type" class="form-select" required>
                          <option value="expense">Despesa (-)</option>
                          <option value="income">Receita (+)</option>
                      </select>
                  </div>
                  <div class="col-md-6 mb-3">
                      <label class="form-label text-muted small text-uppercase">Categoria</label>
                      <select name="category_id" class="form-select">
                          <option value="">(Nenhuma)</option>
                          @foreach($categories as $cat)
                              <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                          @endforeach
                      </select>
                  </div>
              </div>

              <!-- Recorrência (Opcional) -->
              <div class="card bg-dark border-0 mt-2">
                  <div class="card-body py-2">
                      <label class="form-label text-muted small text-uppercase mb-1">Repetir esta transação?</label>
                      <div class="row">
                          <div class="col-6">
                              <select name="recurrence_type" class="form-select form-select-sm">
                                  <option value="">Não (Única)</option>
                                  <option value="monthly">Mensal</option>
                                  <option value="weekly">Semanal</option>
                                  <option value="yearly">Anual</option>
                              </select>
                          </div>
                          <div class="col-6">
                              <input type="number" name="recurrence_installments" class="form-control form-control-sm" placeholder="Ocorrências" min="2" max="120">
                          </div>
                      </div>
                  </div>
              </div>
          </div>
          <div class="modal-footer border-top-0">
              <button type="button" class="btn btn-outline-light" data-bs-dismiss="modal">Cancelar</button>
              <button type="submit" class="btn btn-success">Salvar Transação</button>
          </div>
      </form>
    </div>
  </div>
</div>
@endsection
