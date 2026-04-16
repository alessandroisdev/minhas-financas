@extends('layouts.app')

@section('content')
<nav class="navbar navbar-expand-lg border-bottom" style="border-color: rgba(255,255,255,0.1) !important; background-color: rgba(0,0,0,0.1);">
  <div class="container">
    <a class="navbar-brand text-primary fw-bold" href="#">Minhas Finanças</a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
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
        <div class="col-md-12">
            <div class="card bg-transparent border-0">
                <div class="card-body p-0">
                    <h2 class="fw-bold mb-4">Dashboard</h2>
                    <div class="row g-4">
                        <div class="col-md-4">
                            <div class="card auth-card text-center p-4">
                                <h5 class="text-muted text-uppercase small fw-bold">Saldo Atual</h5>
                                <h3 class="fw-bold text-primary mb-0">R$ 0,00</h3>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="card auth-card text-center p-4">
                                <h5 class="text-muted text-uppercase small fw-bold">Receitas</h5>
                                <h3 class="fw-bold text-success mb-0">R$ 0,00</h3>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="card auth-card text-center p-4">
                                <h5 class="text-muted text-uppercase small fw-bold">Despesas</h5>
                                <h3 class="fw-bold text-danger mb-0">R$ 0,00</h3>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
