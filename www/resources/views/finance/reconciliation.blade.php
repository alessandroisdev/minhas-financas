@extends('layouts.app')

@section('content')
@include('layouts.navbar')

<div class="container">
    <div class="row mb-4">
        <div class="col-12 d-flex justify-content-between align-items-center">
            <h2 class="fw-bold mb-0">Conciliação: {{ $import->filename }}</h2>
            <div>
                <span class="badge text-bg-secondary p-2 me-2">Processados: <span id="progressIndicator">{{ $import->processed_items }}/{{ $import->total_items }}</span></span>
                <span class="badge {{ $import->status == 'completed' ? 'text-bg-success' : 'text-bg-warning' }} p-2" id="statusBadge">Status: {{ strtoupper($import->status) }}</span>
            </div>
        </div>
    </div>

    <!-- Layout Dividido Conciliados X Pendentes -->
    <div class="row">
        <!-- Pendentes -->
        <div class="col-md-7 mb-4">
            <div class="card auth-card border-0">
                <div class="card-header bg-transparent border-0 pt-4 pb-0">
                    <h5 class="fw-bold text-warning mb-0"><i class="bi bi-exclamation-triangle"></i> Atenção Requerida (Não Conciliados)</h5>
                    <p class="small text-muted mb-0">O sistema não encontrou transações exatas para estes itens do extrato.</p>
                </div>
                <div class="card-body">
                    <div class="list-group list-group-flush rounded-3 bg-dark">
                        @forelse($pendingBankTxs as $pendingTx)
                           <div class="list-group-item bg-transparent text-white border-bottom border-secondary py-3">
                               <div class="d-flex w-100 justify-content-between">
                                   <div class="mb-1 fw-bold">{{ $pendingTx->description }}</div>
                                   <div class="fw-bold {{ $pendingTx->amount < 0 ? 'text-danger' : 'text-success' }}">
                                       R$ {{ number_format($pendingTx->amount, 2, ',', '.') }}
                                   </div>
                               </div>
                               <div class="d-flex w-100 justify-content-between align-items-center mt-2">
                                   <small class="text-muted"><i class="bi bi-calendar"></i> {{ \Carbon\Carbon::parse($pendingTx->date)->format('d/m/Y') }}</small>
                                   
                                   <!-- Formulario para associar e aprovar como manual -->
                                   <form action="{{ route('bank.reconciliation.manual', $pendingTx->id) }}" method="POST" class="d-flex align-items-center">
                                       @csrf
                                       <select name="category_id" class="form-select form-select-sm me-2" style="max-width: 200px;" required>
                                           <option value="">Selecione Categoria...</option>
                                           @foreach($categories as $cat)
                                               <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                                           @endforeach
                                       </select>
                                       <button type="submit" class="btn btn-sm btn-outline-primary">Lançar & Conciliar</button>
                                   </form>
                               </div>
                           </div>
                        @empty
                            <div class="p-4 text-center text-muted fw-semibold">
                                @if($import->status == 'processing')
                                    Processando arquivo... aguarde.
                                @else
                                    Nenhum item pendente de ação. Perfeito!
                                @endif
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>

        <!-- Já Conciliados (Automático) -->
        <div class="col-md-5">
            <div class="card auth-card border-0" style="opacity: 0.85;">
                <div class="card-header bg-transparent border-0 pt-4 pb-0">
                    <h5 class="fw-bold text-success mb-0"><i class="bi bi-check-circle"></i> Auto-Matching</h5>
                    <p class="small text-muted mb-0">Itens encontrados e ajustados pela IA.</p>
                </div>
                <div class="card-body">
                    <ul class="list-group list-group-flush rounded-3 bg-dark">
                        @forelse($matchedBankTxs as $matchedTx)
                            <li class="list-group-item bg-transparent text-white border-bottom border-secondary py-2">
                                <div class="d-flex justify-content-between align-items-center">
                                    <span class="small">{{ Str::limit($matchedTx->description, 25) }}</span>
                                    <span class="badge text-bg-success"><i class="bi bi-link-45deg"></i> Conciliado</span>
                                </div>
                            </li>
                        @empty
                            <li class="p-3 text-center text-muted small">Nenhum match automático ainda.</li>
                        @endforelse
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        // Recarregar a página automaticamente se o status global via SSE avisar que o import terminou
        const sseObserver = new EventSource("{{ route('stream') }}");
        sseObserver.onmessage = function(event) {
            try {
                const data = JSON.parse(event.data);
                if(data.type === 'toast' && data.message.includes('Importação concluída')) {
                    // Espera 2 segs pro user ler o toast e da refresh preenchendo as rows
                    setTimeout(() => window.location.reload(), 2000);
                }
            } catch(e) {}
        };
    });
</script>
@endsection
