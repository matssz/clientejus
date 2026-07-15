@extends('layouts.app')

@section('title', 'Clientes | ClienteJus')

@section('content')
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
        <div>
            <p class="text-uppercase text-primary fw-semibold small mb-2">Atendimento</p>
            <h1 class="h3 mb-1">Clientes</h1>
            <p class="text-secondary mb-0">Consulte e mantenha os dados dos seus clientes.</p>
        </div>
        <a class="btn btn-primary" href="{{ route('clientes.create') }}">Novo cliente</a>
    </div>

    <form class="search-bar mb-4" method="GET" action="{{ route('clientes.index') }}">
        <div class="input-group">
            <input
                class="form-control"
                name="search"
                type="search"
                value="{{ $search }}"
                placeholder="Buscar por nome, contato ou documento"
                aria-label="Buscar clientes"
            >
            <button class="btn btn-outline-primary" type="submit">Buscar</button>
        </div>
        @if ($search !== '')
            <a class="small" href="{{ route('clientes.index') }}">Limpar busca</a>
        @endif
    </form>

    @if ($clients->isEmpty())
        <div class="empty-message">
            <p class="mb-1 fw-semibold">
                {{ $search === '' ? 'Nenhum cliente cadastrado.' : 'Nenhum cliente encontrado.' }}
            </p>
            <p class="text-secondary mb-0">
                {{ $search === '' ? 'Cadastre o primeiro cliente para iniciar.' : 'Tente buscar usando outro termo.' }}
            </p>
        </div>
    @else
        <div class="table-responsive border rounded-2 bg-white">
            <table class="table align-middle mb-0">
                <thead>
                    <tr>
                        <th scope="col">Nome</th>
                        <th scope="col">Contato</th>
                        <th scope="col">CPF/CNPJ</th>
                        <th scope="col">Atualizado</th>
                        <th scope="col" class="text-end">Ações</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($clients as $client)
                        <tr>
                            <td class="fw-semibold">{{ $client->name }}</td>
                            <td>
                                <div>{{ $client->email ?: 'E-mail não informado' }}</div>
                                <small class="text-secondary">{{ $client->phone ?: 'Telefone não informado' }}</small>
                            </td>
                            <td>{{ $client->document ?: 'Não informado' }}</td>
                            <td>{{ $client->updated_at->format('d/m/Y') }}</td>
                            <td class="text-end text-nowrap">
                                <a class="btn btn-sm btn-outline-primary" href="{{ route('clientes.show', $client) }}">Ver</a>
                                <a class="btn btn-sm btn-outline-secondary" href="{{ route('clientes.edit', $client) }}">Editar</a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="mt-3">
            {{ $clients->links() }}
        </div>
    @endif
@endsection
