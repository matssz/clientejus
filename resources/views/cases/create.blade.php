@extends('layouts.app')

@section('title', 'Novo caso | ClienteJus')

@section('content')
    <div class="page-narrow">
        <a class="d-inline-block mb-3" href="{{ route('casos.index') }}">Voltar para casos</a>

        <div class="mb-4">
            <p class="text-uppercase text-primary fw-semibold small mb-2">Novo atendimento</p>
            <h1 class="h3 mb-1">Cadastrar caso</h1>
            <p class="text-secondary mb-0">Vincule o atendimento a um cliente e defina o status inicial.</p>
        </div>

        <div class="card form-card">
            <div class="card-body p-4">
                <form method="POST" action="{{ route('casos.store') }}" novalidate>
                    @csrf
                    @include('cases._form')

                    <div class="d-flex flex-column-reverse flex-sm-row justify-content-end gap-2 mt-4">
                        <a class="btn btn-outline-secondary" href="{{ route('casos.index') }}">Cancelar</a>
                        <button class="btn btn-primary" type="submit">Cadastrar caso</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
