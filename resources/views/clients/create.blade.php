@extends('layouts.app')

@section('title', 'Novo cliente | ClienteJus')

@section('content')
    <div class="page-narrow">
        <a class="d-inline-block mb-3" href="{{ route('clientes.index') }}">Voltar para clientes</a>

        <div class="mb-4">
            <p class="text-uppercase text-primary fw-semibold small mb-2">Novo cadastro</p>
            <h1 class="h3 mb-1">Cadastrar cliente</h1>
            <p class="text-secondary mb-0">Informe os dados disponíveis. Apenas o nome é obrigatório.</p>
        </div>

        <div class="card form-card">
            <div class="card-body p-4">
                <form method="POST" action="{{ route('clientes.store') }}" novalidate>
                    @csrf

                    @include('clients._form')

                    <div class="d-flex flex-column-reverse flex-sm-row justify-content-end gap-2 mt-4">
                        <a class="btn btn-outline-secondary" href="{{ route('clientes.index') }}">Cancelar</a>
                        <button class="btn btn-primary" type="submit">Cadastrar cliente</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
