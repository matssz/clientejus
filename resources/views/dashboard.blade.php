@extends('layouts.app')

@section('title', 'Painel | ClienteJus')

@section('content')
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
        <div>
            <p class="text-uppercase text-primary fw-semibold small mb-2">Visão geral</p>
            <h1 class="h3 mb-1">Olá, {{ auth()->user()->name }}</h1>
            <p class="text-secondary mb-0">Acompanhe os dados principais do seu escritório.</p>
        </div>
        <a class="btn btn-primary" href="{{ route('clientes.create') }}">Novo cliente</a>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-12 col-md-4">
            <div class="card stat-card stat-card-primary h-100">
                <div class="card-body">
                    <p class="text-secondary mb-2">Clientes cadastrados</p>
                    <p class="display-6 fw-semibold mb-0">{{ $clientCount }}</p>
                </div>
            </div>
        </div>
        <div class="col-12 col-md-4">
            <div class="card stat-card stat-card-success h-100">
                <div class="card-body">
                    <p class="text-secondary mb-2">Casos cadastrados</p>
                    <p class="display-6 fw-semibold mb-0">{{ $caseCount }}</p>
                </div>
            </div>
        </div>
        <div class="col-12 col-md-4">
            <div class="card stat-card stat-card-warning h-100">
                <div class="card-body">
                    <p class="text-secondary mb-2">Documentos pendentes</p>
                    <p class="display-6 fw-semibold mb-0">{{ $pendingDocumentCount }}</p>
                </div>
            </div>
        </div>
    </div>

    <section class="content-section">
        <div class="d-flex justify-content-between align-items-center gap-3 mb-3">
            <h2 class="h5 mb-0">Clientes recentes</h2>
            <a href="{{ route('clientes.index') }}">Ver todos</a>
        </div>

        @if ($recentClients->isEmpty())
            <div class="empty-message">
                <p class="mb-1 fw-semibold">Nenhum cliente cadastrado.</p>
                <p class="text-secondary mb-0">Cadastre o primeiro cliente para começar a organizar os atendimentos.</p>
            </div>
        @else
            <div class="table-responsive border rounded-2 bg-white">
                <table class="table align-middle mb-0">
                    <thead>
                        <tr>
                            <th scope="col">Nome</th>
                            <th scope="col">Contato</th>
                            <th scope="col" class="text-end">Ação</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($recentClients as $client)
                            <tr>
                                <td class="fw-semibold">{{ $client->name }}</td>
                                <td class="text-secondary">{{ $client->email ?: ($client->phone ?: 'Não informado') }}</td>
                                <td class="text-end">
                                    <a class="btn btn-sm btn-outline-primary" href="{{ route('clientes.show', $client) }}">Ver</a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </section>
@endsection
