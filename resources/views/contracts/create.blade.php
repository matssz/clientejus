@extends('layouts.app')

@section('title', 'Novo contrato | ClienteJus')

@section('content')
    <div class="page-narrow">
        <a class="d-inline-block mb-3" href="{{ route('contratos.index') }}">Voltar para contratos</a>

        <div class="mb-4">
            <p class="text-uppercase text-primary fw-semibold small mb-2">Documento contratual</p>
            <h1 class="h3 mb-1">Cadastrar contrato</h1>
            <p class="text-secondary mb-0">Vincule o contrato a um caso e registre sua vigência.</p>
        </div>

        <div class="card form-card">
            <div class="card-body p-4">
                <form method="POST" action="{{ route('contratos.store') }}" enctype="multipart/form-data" novalidate>
                    @csrf
                    @include('contracts._form')

                    <div class="d-flex flex-column-reverse flex-sm-row justify-content-end gap-2 mt-4">
                        <a class="btn btn-outline-secondary" href="{{ route('contratos.index') }}">Cancelar</a>
                        <button class="btn btn-primary" type="submit">Cadastrar contrato</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
