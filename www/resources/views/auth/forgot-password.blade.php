@extends('layouts.app')

@section('content')
<div class="auth-wrapper">
    <div class="auth-card mx-auto">
        <div class="auth-logo">
            Recuperar Senha
        </div>

        <div class="text-muted text-center small mb-4">
            Esqueceu sua senha? Sem problemas. Basta nos informar seu endereço de e-mail e nós enviaremos um link de redefinição de senha que permitirá que você escolha uma nova.
        </div>

        <form method="POST" action="{{ route('password.email') }}" id="authForm">
            @csrf

            <!-- Email Address -->
            <div class="mb-4">
                <label for="email" class="form-label text-muted fw-semibold small text-uppercase tracking-wider">E-mail</label>
                <input id="email" type="email" class="form-control @error('email') is-invalid @enderror" name="email" value="{{ old('email') }}" required autofocus placeholder="voce@exemplo.com">
                @error('email')
                    <span class="invalid-feedback fw-bold" role="alert">
                        <strong>{{ $message }}</strong>
                    </span>
                @enderror
            </div>

            <div class="d-grid mt-4">
                <button type="submit" id="submitBtn" class="btn btn-primary text-uppercase">
                    Enviar Link de Recuperação
                </button>
            </div>
            
            <div class="text-center mt-4">
                <a class="text-decoration-none small fw-semibold" href="{{ route('login') }}">
                    Voltar para o Login
                </a>
            </div>
        </form>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const form = document.getElementById('authForm');
        const btn = document.getElementById('submitBtn');

        form.addEventListener('submit', function () {
            btn.disabled = true;
            btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span> Processando...';
        });
    });
</script>
@endsection
