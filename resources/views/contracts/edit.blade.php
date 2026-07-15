@extends('layouts.app')

@section('title', 'Editar contrato | ClienteJus')

@section('content')
    <div class="page-narrow">
        <a class="d-inline-block mb-3" href="{{ route('contratos.show', $contract) }}">Voltar para o contrato</a>

        <div class="mb-4">
            <p class="text-uppercase text-primary fw-semibold small mb-2">Atualização contratual</p>
            <h1 class="h3 mb-1">Editar contrato</h1>
            <p class="text-secondary mb-0">Atualize datas, status ou substitua o documento original.</p>
        </div>

        <div class="card form-card">
            <div class="card-body p-4">
                <form method="POST" action="{{ route('contratos.update', $contract) }}" enctype="multipart/form-data" novalidate>
                    @csrf
                    @method('PUT')
                    @include('contracts._form', ['contract' => $contract])

                    <div class="d-flex flex-column-reverse flex-sm-row justify-content-end gap-2 mt-4">
                        <a class="btn btn-outline-secondary" href="{{ route('contratos.show', $contract) }}">Cancelar</a>
                        <button class="btn btn-primary" type="submit">Salvar alterações</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
