@extends('layouts.app')

@section('title', 'Editar caso | ClienteJus')

@section('content')
    <div class="page-narrow">
        <a class="d-inline-block mb-3" href="{{ route('casos.show', $case) }}">Voltar para o caso</a>

        <div class="mb-4">
            <p class="text-uppercase text-primary fw-semibold small mb-2">Atualização</p>
            <h1 class="h3 mb-1">Editar caso</h1>
            <p class="text-secondary mb-0">Atualize os dados e o andamento do atendimento.</p>
        </div>

        <div class="card form-card">
            <div class="card-body p-4">
                <form method="POST" action="{{ route('casos.update', $case) }}" novalidate>
                    @csrf
                    @method('PUT')
                    @include('cases._form', ['case' => $case])

                    <div class="d-flex flex-column-reverse flex-sm-row justify-content-end gap-2 mt-4">
                        <a class="btn btn-outline-secondary" href="{{ route('casos.show', $case) }}">Cancelar</a>
                        <button class="btn btn-primary" type="submit">Salvar alterações</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
