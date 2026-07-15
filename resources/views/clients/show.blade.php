@extends('layouts.app')

@section('title', $client->name . ' | ClienteJus')

@section('content')
    <a class="d-inline-block mb-3" href="{{ route('clientes.index') }}">Voltar para clientes</a>

    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-start gap-3 mb-4">
        <div>
            <p class="text-uppercase text-primary fw-semibold small mb-2">Cliente</p>
            <h1 class="h3 mb-1">{{ $client->name }}</h1>
            <p class="text-secondary mb-0">Cadastrado em {{ $client->created_at->format('d/m/Y') }}</p>
        </div>
        <a class="btn btn-primary" href="{{ route('clientes.edit', $client) }}">Editar cliente</a>
    </div>

    @error('delete')
        <div class="alert alert-danger" role="alert">{{ $message }}</div>
    @enderror

    <section class="content-section client-details mb-4">
        <h2 class="h5 mb-3">Dados de contato</h2>
        <dl class="row mb-0">
            <dt class="col-sm-3">E-mail</dt>
            <dd class="col-sm-9">
                @if ($client->email)
                    <a href="mailto:{{ $client->email }}">{{ $client->email }}</a>
                @else
                    <span class="text-secondary">Não informado</span>
                @endif
            </dd>

            <dt class="col-sm-3">Telefone</dt>
            <dd class="col-sm-9">{{ $client->phone ?: 'Não informado' }}</dd>

            <dt class="col-sm-3">CPF/CNPJ</dt>
            <dd class="col-sm-9">{{ $client->document ?: 'Não informado' }}</dd>
        </dl>
    </section>

    <section class="content-section client-details mb-4">
        <h2 class="h5 mb-3">Observações</h2>
        <p class="mb-0 text-break">{{ $client->notes ?: 'Nenhuma observação cadastrada.' }}</p>
    </section>

    <section class="danger-zone">
        <div>
            <h2 class="h6 mb-1">Excluir cliente</h2>
            <p class="text-secondary small mb-0">Esta ação não poderá ser desfeita.</p>
        </div>
        <form
            method="POST"
            action="{{ route('clientes.destroy', $client) }}"
            onsubmit="return confirm('Tem certeza de que deseja excluir este cliente?')"
        >
            @csrf
            @method('DELETE')
            <button class="btn btn-outline-danger" type="submit">Excluir</button>
        </form>
    </section>
@endsection
