@extends('layouts.app')

@section('title', 'Editar cliente | ClienteJus')

@section('content')
    <div class="page-narrow">
        <a class="d-inline-block mb-3" href="{{ route('clientes.show', $client) }}">Voltar para o cliente</a>

        <div class="mb-4">
            <p class="text-uppercase text-primary fw-semibold small mb-2">Atualização</p>
            <h1 class="h3 mb-1">Editar cliente</h1>
            <p class="text-secondary mb-0">Atualize os dados de {{ $client->name }}.</p>
        </div>

        <div class="card form-card">
            <div class="card-body p-4">
                <form method="POST" action="{{ route('clientes.update', $client) }}" novalidate>
                    @csrf
                    @method('PUT')

                    @include('clients._form', ['client' => $client])

                    <div class="d-flex flex-column-reverse flex-sm-row justify-content-end gap-2 mt-4">
                        <a class="btn btn-outline-secondary" href="{{ route('clientes.show', $client) }}">Cancelar</a>
                        <button class="btn btn-primary" type="submit">Salvar alterações</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
