@extends('layouts.app')

@section('content')
@include('layouts.navbar')

<div class="container-fluid pb-5 px-4">
    <!-- Header Cartão -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <a href="{{ route('credit-cards.index') }}" class="text-info text-decoration-none small fw-bold mb-2 d-inline-block"><i class="bi bi-arrow-left"></i> Voltar aos Cartões</a>
            <h2 class="fw-bold mb-0 text-white d-flex align-items-center">
                <span class="d-inline-block rounded-circle me-3" style="width:20px;height:20px;background-color:{{ $card->color }}; border:2px solid #fff;"></span>
                Fatura {{ $card->name }}
            </h2>
            <p class="text-muted small">Limite disponível: <strong class="text-info">R$ {{ number_format($availableLimit, 2, ',', '.') }}</strong></p>
        </div>
        <button class="btn btn-warning fw-bold px-4" data-bs-toggle="modal" data-bs-target="#newPurchaseModal">
            <i class="bi bi-cart-plus"></i> Lançar Compra
        </button>
    </div>

    @if(session('status'))
        <div class="alert alert-success bg-success text-white border-0">{{ session('status') }}</div>
    @endif
    @if($errors->any())
        <div class="alert alert-danger bg-danger text-white border-0">{{ $errors->first() }}</div>
    @endif

    <!-- Time Shift Control -->
    <div class="d-flex justify-content-center align-items-center mb-4">
        <a href="{{ route('credit-cards.show', ['credit_card' => $card->id, 'offset' => $monthOffset - 1]) }}" class="btn btn-outline-secondary me-3"><i class="bi bi-chevron-left"></i> Anterior</a>
        
        <div class="text-center px-4 py-2 rounded-pill shadow-sm" style="background-color: #1e293b; border: 1px solid #334155;">
            @php
                $statusColor = 'text-info';
                $statusBadge = 'Fatura Aberta';
                
                if (now()->format('Y-m-d') > $endWindow->format('Y-m-d') && $invoiceTotal > 0) {
                    $statusColor = 'text-warning';
                    $statusBadge = 'Fatura Fechada';
                }
                if ($monthOffset > 0) {
                    $statusColor = 'text-muted';
                    $statusBadge = 'Fatura Futura';
                }
            @endphp
            <h4 class="fw-bold mb-0 {{ $statusColor }}">{{ ucfirst($dueDate->translatedFormat('F / Y')) }}</h4>
            <span class="small text-white-50">Vence em {{ $dueDate->format('d/m/Y') }} &bull; <span class="{{ $statusColor }} fw-bold">{{ $statusBadge }}</span></span>
        </div>
        
        <a href="{{ route('credit-cards.show', ['credit_card' => $card->id, 'offset' => $monthOffset + 1]) }}" class="btn btn-outline-secondary ms-3">Futura <i class="bi bi-chevron-right"></i></a>
    </div>

    <div class="row">
        <div class="col-md-8 mx-auto">
            
            <div class="card bg-dark border-secondary shadow-lg">
                <div class="card-header bg-black bg-opacity-25 border-secondary py-4 text-center">
                    <p class="text-muted text-uppercase fw-bold small mb-1">Total da Fatura</p>
                    <h1 class="display-3 fw-bold mb-0 {{ $invoiceTotal > 0 ? 'text-danger' : 'text-success' }}">
                        R$ {{ number_format($invoiceTotal, 2, ',', '.') }}
                    </h1>
                    @if($statusBadge === 'Fatura Fechada')
                        <form action="{{ route('credit-cards.payInvoice', $card->id) }}" method="POST" class="mt-4" onsubmit="return confirm('Deseja abater os R$ {{ number_format($invoiceTotal, 2) }} do seu Caixa Central em 1 único lançamento e marcar essa fatura como paga?')">
                            @csrf
                            <input type="hidden" name="invoice_total" value="{{ $invoiceTotal }}">
                            <button class="btn btn-success fw-bold px-5 rounded-pill shadow-lg" style="letter-spacing: 1px;"><i class="bi bi-check-circle-fill"></i> Liquidar Fatura no Caixa</button>
                        </form>
                    @endif
                </div>
                
                <div class="card-body p-0">
                    <table class="table table-dark table-hover mb-0">
                        <tbody>
                            @forelse($invoiceTransactions as $tx)
                            <tr class="align-middle">
                                <td class="ps-4 text-white-50" style="width: 15%;">
                                    <span class="fs-5 d-block">{{ \Carbon\Carbon::parse($tx->date)->format('d') }}</span>
                                    <small class="text-uppercase">{{ \Carbon\Carbon::parse($tx->date)->translatedFormat('M') }}</small>
                                </td>
                                <td>
                                    <span class="fw-bold fs-6">{{ $tx->description }}</span>
                                    @if($tx->category)
                                        <div class="small mt-1"><span class="badge" style="background-color:{{ $tx->category->color }};">{{ $tx->category->name }}</span></div>
                                    @endif
                                </td>
                                <td class="text-end pe-4">
                                    <h5 class="fw-bold mb-0 text-white">R$ {{ number_format($tx->amount, 2, ',', '.') }}</h5>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="3" class="text-center py-5 text-muted">
                                    <i class="bi bi-emoji-smile display-4 d-block mb-3" style="opacity:0.3;"></i>
                                    <h5>Nenhuma compra registrada nesta fatura.</h5>
                                    <p>O período compreende de {{ $startWindow->format('d/m') }} até {{ $endWindow->format('d/m') }}.</p>
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

<!-- Modal Nova Compra (Cartão) -->
<div class="modal fade" id="newPurchaseModal" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content bg-dark border-0 shadow-lg text-white">
      <div class="modal-header border-secondary border-bottom">
        <h5 class="modal-title fw-bold text-warning"><i class="bi bi-cart"></i> Nova Compra - {{ $card->brand }}</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
      </div>
      <form action="{{ route('credit-cards.storeTransaction', $card->id) }}" method="POST">
          @csrf
          <div class="modal-body p-4">
              <div class="mb-3">
                  <label class="form-label text-muted small text-uppercase">Estabelecimento / Descrição</label>
                  <input type="text" name="description" class="form-control bg-black border-secondary text-white" required placeholder="Ex: Ifood, Mercado">
              </div>
              
              <div class="row">
                  <div class="col-md-6 mb-3">
                      <label class="form-label text-muted small text-uppercase">Valor Total (R$)</label>
                      <input type="number" step="0.01" name="amount" class="form-control bg-black border-secondary text-white" required placeholder="0.00">
                  </div>
                  <div class="col-md-6 mb-3">
                      <label class="form-label text-muted small text-uppercase">Data da Compra</label>
                      <input type="date" name="date" class="form-control bg-black border-secondary text-white" value="{{ date('Y-m-d') }}" required>
                  </div>
              </div>

              <div class="row">
                  <div class="col-6 mb-3">
                      <label class="form-label text-muted small text-uppercase">Parcelas</label>
                      <select name="installments" class="form-select bg-black border-secondary text-white fw-bold text-info">
                          <option value="1">1x (À vista)</option>
                          @for($i=2; $i<=24; $i++)
                              <option value="{{ $i }}">{{ $i }}x Sem Juros</option>
                          @endfor
                      </select>
                  </div>
                  <div class="col-6 mb-3">
                      <!-- Exemplo estático de bind category se existir a variavel, mas no scope atual vamos deixar em branco ou pre-carregar -->
                      <label class="form-label text-muted small text-uppercase">Categoria</label>
                      <select name="category_id" class="form-select bg-black border-secondary text-white">
                          <option value="">(Opcional)</option>
                          <!-- As categorias seriam passadas do controller se optarmos injetar na view, ignorado p/ mock limpo -->
                      </select>
                  </div>
              </div>
              <p class="text-muted small mt-2"><i class="bi bi-info-circle"></i> Compras feitas após o dia {{ $card->closing_day }} virarão para o mês que vem automaticamente pela IA.</p>
          </div>
          <div class="modal-footer border-secondary border-top">
              <button type="submit" class="btn btn-warning w-100 fw-bold shadow-lg text-dark">Lançar no Cartão</button>
          </div>
      </form>
    </div>
  </div>
</div>
@endsection
