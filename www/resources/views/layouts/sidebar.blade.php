<div class="card bg-dark mb-4 border-0 shadow-lg d-none d-md-block" style="background-color: rgba(0,0,0,0.2) !important;">
    <div class="card-body p-4">
        <h6 class="text-uppercase text-muted fw-bold small mb-3">Financeiro</h6>
        <div class="d-grid gap-2">
            <a href="{{ route('dashboard') }}" class="btn {{ Route::is('dashboard') ? 'btn-primary' : 'btn-outline-light' }} text-start">
                <i class="bi bi-graph-up me-2"></i> Painel Geral
            </a>
            
            <!-- Receitas (Filtro) -->
            <a href="{{ route('transactions.index', ['type' => 'income']) }}" class="btn {{ request('type') == 'income' ? 'btn-primary' : 'btn-outline-light' }} text-start">
                <i class="bi bi-arrow-up-circle text-success me-2"></i> Receitas
            </a>
            
            <!-- Despesas (Filtro) -->
            <a href="{{ route('transactions.index', ['type' => 'expense']) }}" class="btn {{ request('type') == 'expense' ? 'btn-primary' : 'btn-outline-light' }} text-start">
                <i class="bi bi-arrow-down-circle text-danger me-2"></i> Despesas
            </a>

            <!-- Todas as Transações -->
            <a href="{{ route('transactions.index') }}" class="btn {{ Route::is('transactions.*') && !request()->has('type') ? 'btn-primary' : 'btn-outline-light' }} text-start">
                <i class="bi bi-wallet2 me-2"></i> Extrato Completo
            </a>
            
            <a href="{{ route('categories.index') }}" class="btn {{ Route::is('categories.*') ? 'btn-primary' : 'btn-outline-light' }} text-start">
                <i class="bi bi-tags me-2"></i> Categorias
            </a>

            <!-- Modal Acionador (sem class active pq é um botão popup) -->
            <button class="btn btn-outline-light text-start" data-bs-toggle="modal" data-bs-target="#importModal">
                <i class="bi bi-bank me-2"></i> Conciliação Bancária
            </button>
        </div>
    </div>
</div>
