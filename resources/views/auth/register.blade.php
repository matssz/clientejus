@extends('layouts.app')

@section('title', 'Criar conta | ClienteJus')

@section('content')
    <div class="auth-shell">
        <div class="auth-heading mb-4">
            <p class="text-uppercase text-success fw-semibold small mb-2">Primeiro acesso</p>
            <h1 class="h3 mb-2">Crie sua conta</h1>
            <p class="text-secondary mb-0">Comece a organizar sua rotina jurídica.</p>
        </div>

        <div class="card auth-card shadow-sm">
            <div class="card-body p-4">
                <form method="POST" action="{{ route('register') }}" novalidate>
                    @csrf

                    <div class="mb-3">
                        <label class="form-label" for="name">Nome</label>
                        <input
                            class="form-control @error('name') is-invalid @enderror"
                            id="name"
                            name="name"
                            type="text"
                            value="{{ old('name') }}"
                            autocomplete="name"
                            autofocus
                            required
                        >
                        @error('name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label" for="email">E-mail</label>
                        <input
                            class="form-control @error('email') is-invalid @enderror"
                            id="email"
                            name="email"
                            type="email"
                            value="{{ old('email') }}"
                            autocomplete="email"
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
                            autocomplete="new-password"
                            required
                        >
                        <div class="form-text">Use ao menos 8 caracteres, com letras e números.</div>
                        @error('password')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-4">
                        <label class="form-label" for="password_confirmation">Confirmar senha</label>
                        <input
                            class="form-control"
                            id="password_confirmation"
                            name="password_confirmation"
                            type="password"
                            autocomplete="new-password"
                            required
                        >
                    </div>

                    <button class="btn btn-success w-100" type="submit">Criar conta</button>
                </form>
            </div>
        </div>

        <p class="text-center text-secondary mt-4 mb-0">
            Já possui uma conta?
            <a href="{{ route('login') }}">Entrar</a>
        </p>
    </div>
@endsection
