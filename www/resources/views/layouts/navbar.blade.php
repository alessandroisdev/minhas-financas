<nav class="navbar navbar-expand-lg border-bottom mb-5" style="border-color: rgba(255,255,255,0.1) !important; background-color: rgba(0,0,0,0.1);">
  <div class="container">
    <a class="navbar-brand text-primary fw-bold" href="{{ route('dashboard') }}">Minhas Finanças</a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
      <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="navbarNav">
      <ul class="navbar-nav me-auto mt-3 mt-lg-0">
        <li class="nav-item"><a class="nav-link fw-bold" href="{{ route('dashboard') }}">Painel</a></li>
        <li class="nav-item"><a class="nav-link text-success fw-bold" href="{{ route('transactions.index', ['type' => 'income']) }}">Receitas</a></li>
        <li class="nav-item"><a class="nav-link text-danger fw-bold" href="{{ route('transactions.index', ['type' => 'expense']) }}">Despesas</a></li>
        <li class="nav-item"><a class="nav-link" href="{{ route('transactions.index') }}">Extrato</a></li>
        <li class="nav-item"><a class="nav-link" href="{{ route('categories.index') }}">Categorias</a></li>
        <li class="nav-item"><a class="nav-link text-info" href="{{ route('documents.index') }}"><i class="bi bi-safe2"></i> Cofre</a></li>
        <li class="nav-item">
            <a class="nav-link text-warning" href="#" data-bs-toggle="modal" data-bs-target="#importModal">Conciliação</a>
        </li>
      </ul>
      
      <ul class="navbar-nav ms-auto border-top pt-2 mt-2 border-secondary d-lg-flex border-lg-0 pt-lg-0 mt-lg-0">
        <li class="nav-item">
            <span class="nav-link text-white-50"><i class="bi bi-person-circle text-primary"></i> Olá, {{ Auth::user()->name }}</span>
        </li>
        <li class="nav-item ps-lg-3">
            <form action="{{ route('logout') }}" method="POST">
                @csrf
                <button type="submit" class="btn btn-sm btn-outline-danger mt-1 w-100"><i class="bi bi-box-arrow-right"></i> Sair</button>
            </form>
        </li>
      </ul>
    </div>
  </div>
</nav>

<!-- Modal Global Conciliação (Para poder ser acionado de qualquer lugar da sidebar) -->
<div class="modal fade" id="importModal" tabindex="-1" aria-labelledby="importModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content bg-dark border-0 shadow-lg" style="background-color: #0F172A !important;">
      <div class="modal-header border-bottom-0">
        <h1 class="modal-title fs-5 fw-bold" id="importModalLabel">Importar Transações</h1>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <form action="{{ route('bank.import') }}" method="POST" enctype="multipart/form-data" id="globalImportForm">
          @csrf
          <div class="modal-body">
             <div class="mb-3">
                 <label class="form-label text-muted small text-uppercase">Extrato Bancário (.ofx / .csv)</label>
                 <input class="form-control" type="file" name="ofx_file" required accept=".ofx,.csv">
                 <div class="form-text text-white-50 small mt-2">
                     Apenas anexar o arquivo. A IA de fundo filtrará receitas, despesas e matches sem congelar sua tela.
                 </div>
             </div>
          </div>
          <div class="modal-footer border-top-0">
            <button type="button" class="btn btn-outline-light" data-bs-dismiss="modal">Cancelar</button>
            <button type="submit" id="btnGlobalImport" class="btn btn-primary">Processar no Background</button>
          </div>
      </form>
    </div>
  </div>
</div>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const importForm = document.getElementById('globalImportForm');
        if(importForm) {
            importForm.addEventListener('submit', function() {
                const btn = document.getElementById('btnGlobalImport');
                btn.disabled = true;
                btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span> Enviando...';
            });
        }
    });
</script>
