@extends('layouts.app')

@section('title', 'Entrar | ClienteJus')

@section('content')
    <div class="auth-shell">
        <div class="auth-heading mb-4">
            <p class="text-uppercase text-primary fw-semibold small mb-2">Acesso seguro</p>
            <h1 class="h3 mb-2">Entre no ClienteJus</h1>
            <p class="text-secondary mb-0">Acesse seus clientes, casos e documentos.</p>
        </div>

        <div class="card auth-card shadow-sm">
            <div class="card-body p-4">
                <form method="POST" action="{{ route('login') }}" novalidate>
                    @csrf

                    <div class="mb-3">
                        <label class="form-label" for="email">E-mail</label>
                        <input
                            class="form-control @error('email') is-invalid @enderror"
                            id="email"
                            name="email"
                            type="email"
                            value="{{ old('email') }}"
                            autocomplete="email"
                            autofocus
                            required
                        >
                        @error('email')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label" for="password">Senha</label>
                        <input
                            class="form-control @error('password') is-invalid @enderror"
                            id="password"
                            name="password"
                            type="password"
                            autocomplete="current-password"
                            required
                        >
                        @error('password')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="form-check mb-4">
                        <input class="form-check-input" id="remember" name="remember" type="checkbox" value="1">
                        <label class="form-check-label" for="remember">Manter conectado</label>
                    </div>

                    <button class="btn btn-primary w-100" type="submit">Entrar</button>
                </form>
            </div>
        </div>

        <p class="text-center text-secondary mt-4 mb-0">
            Ainda não possui uma conta?
            <a href="{{ route('register') }}">Criar conta</a>
        </p>
    </div>
@endsection
