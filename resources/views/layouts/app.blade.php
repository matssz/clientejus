<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', 'ClienteJus')</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body>
    <nav class="navbar navbar-expand-md navbar-dark app-navbar">
        <div class="container">
            <a class="navbar-brand fw-semibold" href="{{ route('dashboard') }}">ClienteJus</a>

            @auth
                <button
                    class="navbar-toggler"
                    type="button"
                    data-bs-toggle="collapse"
                    data-bs-target="#mainNavigation"
                    aria-controls="mainNavigation"
                    aria-expanded="false"
                    aria-label="Abrir navegação"
                >
                    <span class="navbar-toggler-icon"></span>
                </button>

                <div class="collapse navbar-collapse" id="mainNavigation">
                    <ul class="navbar-nav me-auto">
                        <li class="nav-item">
                            <a class="nav-link active" href="{{ route('dashboard') }}">Painel</a>
                        </li>
                    </ul>

                    <div class="d-flex align-items-md-center gap-3 py-3 py-md-0">
                        <span class="navbar-text">{{ auth()->user()->name }}</span>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button class="btn btn-outline-light btn-sm" type="submit">Sair</button>
                        </form>
                    </div>
                </div>
            @endauth
        </div>
    </nav>

    <main class="py-4 py-md-5">
        <div class="container">
            @if (session('status'))
                <div class="alert alert-success" role="alert">{{ session('status') }}</div>
            @endif

            @yield('content')
        </div>
    </main>
</body>
</html>
