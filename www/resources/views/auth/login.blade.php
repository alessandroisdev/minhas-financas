@extends('layouts.app')

@section('content')
<div class="auth-wrapper">
    <div class="auth-card mx-auto">
        <div class="auth-logo">
            Minhas Finanças
        </div>

        <form method="POST" action="{{ url('/login') }}" id="authForm">
            @csrf

            <!-- Login (E-mail ou Username) -->
            <div class="mb-4">
                <label for="login" class="form-label text-muted fw-semibold small text-uppercase tracking-wider">E-mail ou Usuário</label>
                <input id="login" type="text" class="form-control @error('login') is-invalid @enderror" name="login" value="{{ old('login') }}" required autofocus placeholder="Digite seu e-mail ou nome de usuário">
                @error('login')
                    <span class="invalid-feedback fw-bold" role="alert">
                        <strong>{{ $message }}</strong>
                    </span>
                @enderror
            </div>

            <!-- Password -->
            <div class="mb-4">
                <div class="d-flex justify-content-between align-items-center">
                    <label for="password" class="form-label text-muted fw-semibold small text-uppercase tracking-wider mb-0">Senha</label>
                    @if (Route::has('password.request'))
                        <a class="text-decoration-none small fw-semibold" href="{{ route('password.request') }}">
                            Esqueci a senha
                        </a>
                    @endif
                </div>
                <input id="password" type="password" class="form-control mt-2 @error('password') is-invalid @enderror" name="password" required autocomplete="current-password" placeholder="Digite sua senha">
                @error('password')
                    <span class="invalid-feedback d-block fw-bold" role="alert">
                        <strong>{{ $message }}</strong>
                    </span>
                @enderror
            </div>

            <!-- Remember Me -->
            <div class="mb-4 form-check">
                <input class="form-check-input" type="checkbox" name="remember" id="remember" {{ old('remember') ? 'checked' : '' }}>
                <label class="form-check-label text-muted small" for="remember">
                    Lembrar de mim
                </label>
            </div>

            <!-- Submit Button -->
            <div class="d-grid mt-5">
                <button type="submit" id="submitBtn" class="btn btn-primary text-uppercase">
                    Entrar
                </button>
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
            btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span> Acessando...';
        });
    });
</script>
@endsection
