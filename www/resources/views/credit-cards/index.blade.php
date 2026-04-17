@extends('layouts.app')

@section('content')
@include('layouts.navbar')

<div class="container-fluid pb-5 px-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="fw-bold mb-0 text-info"><i class="bi bi-credit-card-2-front"></i> Meus Cartões</h2>
            <p class="text-muted small">Gerencie as suas faturas de forma isolada, não misturando seu caixa sem necessidade.</p>
        </div>
        <button class="btn btn-primary fw-bold" data-bs-toggle="modal" data-bs-target="#newCardModal">
            <i class="bi bi-plus-lg"></i> Novo Cartão
        </button>
    </div>

    @if(session('status'))
        <div class="alert alert-success bg-success text-white border-0">{{ session('status') }}</div>
    @endif

    <div class="row g-4">
        @forelse($cards as $card)
        <div class="col-md-4">
            <a href="{{ route('credit-cards.show', $card->id) }}" class="text-decoration-none">
                <div class="card bg-dark shadow-lg border-0 h-100 position-relative cartao-card overflow-hidden">
                    <div class="card-body p-4 z-2 position-relative">
                        <div class="d-flex justify-content-between">
                            <h5 class="fw-bold text-white mb-0">{{ $card->name }}</h5>
                            <i class="bi bi-credit-card fs-4 text-white-50"></i>
                        </div>
                        <p class="small text-white-50 mb-4">{{ $card->brand }}</p>

                        <!-- Somatoria rough de usage - pode ser dinamizado dps via attribute -->
                        @php
                            $usage = \App\Models\CreditCardTransaction::where('credit_card_id', $card->id)
                                        ->where('date', '>=', now()->subDays(40))
                                        ->sum('amount');
                            $pct = $card->limit > 0 ? ($usage / $card->limit) * 100 : 0;
                            if($pct>100) $pct = 100;
                        @endphp

                        <div class="mt-auto">
                            <div class="d-flex justify-content-between small text-white-50 mb-1">
                                <span>Disponível: <strong>R$ {{ number_format($card->limit - $usage, 2, ',', '.') }}</strong></span>
                                <span>R$ {{ number_format($card->limit, 2, ',', '.') }}</span>
                            </div>
                            <div class="progress bg-black" style="height: 6px;">
                                <div class="progress-bar {{ $pct > 80 ? 'bg-danger' : 'bg-info' }}" role="progressbar" style="width: {{ $pct }}%"></div>
                            </div>
                            
                            <div class="mt-3 pt-3 border-top border-secondary d-flex justify-content-between small text-white-50">
                                <span><i class="bi bi-scissors"></i> Fecha dia {{ $card->closing_day }}</span>
                                <span><i class="bi bi-calendar-check"></i> Vence dia {{ $card->due_day }}</span>
                            </div>
                        </div>
                    </div>
                    <!-- Cor de Fundo Decorativa -->
                    <div class="position-absolute w-100 h-100 top-0 start-0 z-1" style="background: linear-gradient(135deg, {{ $card->color }}88, #111 80%);"></div>
                </div>
            </a>
        </div>
        @empty
        <div class="col-12">
            <div class="card bg-dark border-secondary p-5 text-center">
                <i class="bi bi-credit-card text-muted display-1 mb-3"></i>
                <h4 class="text-white">Nenhum cartão cadastrado</h4>
                <p class="text-muted">Adicione seu primeiro cartão de crédito para acompanhar faturas e limites.</p>
            </div>
        </div>
        @endforelse
    </div>
</div>

<!-- Modal Novo Cartão -->
<div class="modal fade" id="newCardModal" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content bg-dark border-0 shadow-lg text-white">
      <div class="modal-header border-secondary border-bottom">
        <h5 class="modal-title fw-bold">Cadastrar Cartão</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
      </div>
      <form action="{{ route('credit-cards.store') }}" method="POST">
          @csrf
          <div class="modal-body p-4">
              <div class="mb-3">
                  <label class="form-label text-muted small">Nome (Ex: Nubank, Itaucard)</label>
                  <input type="text" name="name" class="form-control bg-black border-secondary text-white" required>
              </div>
              <div class="row">
                  <div class="col-12 mb-3">
                      <label class="form-label text-muted small">Limite Total (R$)</label>
                      <input type="number" step="0.01" name="limit" class="form-control bg-black border-secondary text-white" required>
                  </div>
              </div>
              <div class="row">
                  <div class="col-6 mb-3">
                      <label class="form-label text-muted small">Dia de Fechamento</label>
                      <input type="number" name="closing_day" class="form-control bg-black border-secondary text-white" min="1" max="31" required placeholder="Ex: 25">
                  </div>
                  <div class="col-6 mb-3">
                      <label class="form-label text-muted small">Dia de Vencimento</label>
                      <input type="number" name="due_day" class="form-control bg-black border-secondary text-white" min="1" max="31" required placeholder="Ex: 5">
                  </div>
              </div>
              <div class="row">
                  <div class="col-8 mb-3">
                      <label class="form-label text-muted small">Bandeira</label>
                      <select name="brand" class="form-select bg-black border-secondary text-white">
                          <option value="MasterCard">MasterCard</option>
                          <option value="Visa">Visa</option>
                          <option value="Amex">Amex</option>
                          <option value="Elo">Elo</option>
                      </select>
                  </div>
                  <div class="col-4 mb-3">
                      <label class="form-label text-muted small">Cor</label>
                      <input type="color" name="color" class="form-control form-control-color w-100 bg-black border-secondary" value="#8A05BE">
                  </div>
              </div>
          </div>
          <div class="modal-footer border-secondary border-top">
              <button type="submit" class="btn btn-primary w-100 fw-bold">Salvar Cartão</button>
          </div>
      </form>
    </div>
  </div>
</div>

<style>
.cartao-card { transition: transform 0.2s; cursor: pointer; }
.cartao-card:hover { transform: translateY(-5px); }
</style>
@endsection
