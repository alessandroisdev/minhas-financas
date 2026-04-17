@extends('layouts.app')

@section('content')
@include('layouts.navbar')

<div class="container-fluid pb-5 px-4">
    <div class="row mb-4 align-items-center">
        <div class="col-md-8">
            <h2 class="fw-bold mb-0 text-warning"><i class="bi bi-arrow-left-right"></i> Arena de Conciliação</h2>
            <p class="text-muted small">Alinhe seu fluxo financeiro nativo com os extratos consolidados OFX do seu banco.</p>
        </div>
        <div class="col-md-4 text-end">
            <!-- Botão de subida de OFX se necessário -->
            <button class="btn btn-primary fw-bold px-4" data-bs-toggle="modal" data-bs-target="#importModal">
                <i class="bi bi-cloud-upload"></i> Subir OFX
            </button>
        </div>
    </div>

    @if(session('status'))
        <div class="alert alert-success alert-dismissible bg-success text-white border-0 fade show" role="alert">
            <i class="bi bi-check-circle-fill me-2"></i> {{ session('status') }}
            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="alert"></button>
        </div>
    @endif
    @if($errors->any())
        <div class="alert alert-danger alert-dismissible bg-danger text-white border-0 fade show" role="alert">
            <i class="bi bi-exclamation-triangle-fill me-2"></i> {{ $errors->first() }}
            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="row g-4">
        
        <!-- COLUNA ESQUERDA: EXTRATO DO BANCO -->
        <div class="col-md-6">
            <div class="card bg-dark border-0 shadow-lg h-100">
                <div class="card-header border-bottom border-secondary bg-dark pt-4 pb-3">
                    <h5 class="fw-bold m-0 text-white"><i class="bi bi-bank text-info"></i> Lançamentos Bancários (Pendente)</h5>
                    <small class="text-muted">Deslize para a direita ou absorva no botão.</small>
                </div>
                <div class="card-body p-3 bg-opacity-10 bg-black overflow-auto" style="max-height: 70vh;">
                    @forelse($pendingBankTransactions as $btx)
                    <div class="card bg-dark border-secondary mb-3 ofx-card shadow-sm" draggable="true" data-id="{{ $btx->id }}" id="ofx-{{ $btx->id }}">
                        <div class="card-body p-3">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h6 class="fw-bold mb-1 {{ $btx->amount < 0 ? 'text-danger' : 'text-success' }}">
                                        R$ {{ number_format($btx->amount, 2, ',', '.') }}
                                    </h6>
                                    <p class="mb-0 text-white small text-truncate" style="max-width:250px;" title="{{ $btx->description }}">
                                        {{ $btx->description }}
                                    </p>
                                    <span class="badge text-bg-secondary mt-2">{{ \Carbon\Carbon::parse($btx->date)->format('d/m/Y') }}</span>
                                </div>
                                <div class="text-end">
                                    <form action="{{ route('reconciliation.fastCreate') }}" method="POST">
                                        @csrf
                                        <input type="hidden" name="bank_transaction_id" value="{{ $btx->id }}">
                                        <button type="submit" class="btn btn-sm btn-outline-info fw-bold" title="Criar Lançamento no Sistema">
                                            <i class="bi bi-lightning-charge-fill"></i> Absorver
                                        </button>
                                    </form>
                                    <div class="mt-2 text-muted x-drag-indicator" style="cursor: grab;">
                                        <i class="bi bi-grip-horizontal"></i> Segure p/ Arrastar
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    @empty
                    <div class="text-center text-muted py-5 mt-4">
                        <i class="bi bi-check2-all display-1 d-block mb-3" style="opacity:0.2;"></i>
                        <h5>Nenhuma divergência.</h5>
                        <p>O seu extrato OFX bateu perfeitamente com sua base.</p>
                    </div>
                    @endforelse
                </div>
            </div>
        </div>

        <!-- COLUNA DIREITA: CAIXAS DA EMPRESA -->
        <div class="col-md-6">
            <div class="card bg-dark border-0 shadow-lg h-100">
                <div class="card-header border-bottom border-secondary bg-dark pt-4 pb-3">
                    <h5 class="fw-bold m-0 text-white"><i class="bi bi-building text-warning"></i> Meu Caixa (Espera)</h5>
                    <small class="text-muted">Solte o OFX em cima de uma destas notas para liquidar as duas simultâneamente.</small>
                </div>
                <div class="card-body p-3 bg-opacity-10 bg-black overflow-auto" style="max-height: 70vh;">
                    @forelse($unreconciledTransactions as $tx)
                    <div class="card bg-dark border-secondary mb-3 mybase-card shadow-sm position-relative" data-id="{{ $tx->id }}">
                        <div class="card-body p-3">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h6 class="fw-bold mb-1 {{ $tx->type == 'expense' ? 'text-danger' : 'text-success' }}">
                                        R$ {{ number_format($tx->amount, 2, ',', '.') }}
                                    </h6>
                                    <p class="mb-0 text-white small text-truncate" style="max-width:250px;">
                                        {{ $tx->description }}
                                    </p>
                                    <span class="badge {{ $tx->type == 'expense' ? 'text-bg-danger' : 'text-bg-success' }} mt-2">
                                        {{ \Carbon\Carbon::parse($tx->date)->format('d/m/Y') }}
                                    </span>
                                </div>
                                <div class="text-end">
                                    <span class="badge text-bg-warning rounded-pill">Aguardando Baixa <i class="bi bi-hourglass-split"></i></span>
                                </div>
                            </div>
                        </div>
                        <!-- Drop Overlay Invisible -->
                        <div class="position-absolute top-0 start-0 w-100 h-100 bg-warning bg-opacity-25 rounded d-none flex-column align-items-center justify-content-center z-3 drop-overlay" style="border: 2px dashed #ffc107;">
                            <i class="bi bi-link-45deg display-4 text-warning"></i>
                            <h6 class="text-warning fw-bold">Solte para Vincular</h6>
                        </div>
                    </div>
                    @empty
                    <div class="text-center text-muted py-5 mt-4">
                        <i class="bi bi-inboxes display-1 d-block mb-3" style="opacity:0.2;"></i>
                        <h5>Nenhum lançamento aguardando baixa.</h5>
                    </div>
                    @endforelse
                </div>
            </div>
        </div>

    </div>
</div>

<!-- Hidden Form to POST matcher -->
<form id="matchForm" action="{{ route('reconciliation.match') }}" method="POST" class="d-none">
    @csrf
    <input type="hidden" name="bank_transaction_id" id="hiddenOfxId">
    <input type="hidden" name="transaction_id" id="hiddenBaseId">
</form>

<style>
.ofx-card { transition: transform 0.2s; }
.ofx-card.dragging { opacity: 0.5; transform: scale(0.95); z-index: 1000; border-color: #0dcaf0 !important; }
.mybase-card.drag-over .drop-overlay { display: flex !important; }
::-webkit-scrollbar { width: 8px; } ::-webkit-scrollbar-track { background: #0f172a; } ::-webkit-scrollbar-thumb { background: #334155; border-radius: 4px; }
</style>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const draggables = document.querySelectorAll('.ofx-card');
    const dropzones = document.querySelectorAll('.mybase-card');
    const matchForm = document.getElementById('matchForm');
    const ofxInput = document.getElementById('hiddenOfxId');
    const baseInput = document.getElementById('hiddenBaseId');

    draggables.forEach(draggable => {
        draggable.addEventListener('dragstart', (e) => {
            draggable.classList.add('dragging');
            e.dataTransfer.setData('text/plain', draggable.getAttribute('data-id'));
        });

        draggable.addEventListener('dragend', () => {
            draggable.classList.remove('dragging');
            dropzones.forEach(dz => dz.classList.remove('drag-over'));
        });
    });

    dropzones.forEach(zone => {
        zone.addEventListener('dragover', (e) => {
            e.preventDefault();
            zone.classList.add('drag-over');
        });

        zone.addEventListener('dragleave', () => {
            zone.classList.remove('drag-over');
        });

        zone.addEventListener('drop', (e) => {
            e.preventDefault();
            zone.classList.remove('drag-over');
            
            const bankTxId = e.dataTransfer.getData('text/plain');
            const myBaseTxId = zone.getAttribute('data-id');

            if(bankTxId && myBaseTxId) {
                ofxInput.value = bankTxId;
                baseInput.value = myBaseTxId;

                // Loading visual nas duas cartas
                zone.style.opacity = '0.5';
                document.getElementById('ofx-'+bankTxId).style.opacity = '0.5';

                matchForm.submit();
            }
        });
    });
});
</script>

@endsection
