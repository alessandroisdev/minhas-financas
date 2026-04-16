@extends('layouts.app')

@section('content')
<div class="auth-wrapper">
    <div class="auth-card mx-auto">
        <div class="auth-logo">
            Criar Nova Senha
        </div>

        <form method="POST" action="{{ route('password.update') }}" id="authForm">
            @csrf

            <!-- Password Reset Token -->
            <input type="hidden" name="token" value="{{ $token }}">

            <!-- Email Address -->
            <div class="mb-4">
                <label for="email" class="form-label text-muted fw-semibold small text-uppercase tracking-wider">E-mail</label>
                <input id="email" type="email" class="form-control @error('email') is-invalid @enderror" name="email" value="{{ $email ?? old('email') }}" required autofocus>
                @error('email')
                    <span class="invalid-feedback fw-bold" role="alert">
                        <strong>{{ $message }}</strong>
                    </span>
                @enderror
            </div>

            <!-- Password -->
            <div class="mb-4">
                <label for="password" class="form-label text-muted fw-semibold small text-uppercase tracking-wider">Nova Senha</label>
                <input id="password" type="password" class="form-control @error('password') is-invalid @enderror" name="password" required>
                @error('password')
                    <span class="invalid-feedback fw-bold" role="alert">
                        <strong>{{ $message }}</strong>
                    </span>
                @enderror
            </div>

            <!-- Confirm Password -->
            <div class="mb-4">
                <label for="password-confirm" class="form-label text-muted fw-semibold small text-uppercase tracking-wider">Confirme a Nova Senha</label>
                <input id="password-confirm" type="password" class="form-control" name="password_confirmation" required>
            </div>

            <div class="d-grid mt-5">
                <button type="submit" id="submitBtn" class="btn btn-primary text-uppercase">
                    Redefinir Senha
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
            btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span> Redefinindo...';
        });
    });
</script>
@endsection
