@extends('layouts.app')

@section('title', $case->title . ' | ClienteJus')

@section('content')
    <a class="d-inline-block mb-3" href="{{ route('casos.index') }}">Voltar para casos</a>

    <div class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-start gap-3 mb-4">
        <div>
            <div class="d-flex flex-wrap align-items-center gap-2 mb-2">
                <p class="text-uppercase text-primary fw-semibold small mb-0">Caso jurídico</p>
                <x-status-badge :status="$case->status" :label="$case->statusLabel()" />
            </div>
            <h1 class="h3 mb-1">{{ $case->title }}</h1>
            <p class="text-secondary mb-0">
                Cliente:
                <a href="{{ route('clientes.show', $case->client) }}">{{ $case->client->name }}</a>
            </p>
        </div>
        <div class="d-flex flex-column flex-sm-row gap-2">
            @if ($case->client->phone)
                <a
                    class="btn btn-success"
                    href="{{ route('casos.whatsapp', $case) }}"
                    target="_blank"
                    rel="noopener noreferrer"
                >Abrir WhatsApp</a>
            @else
                <a class="btn btn-outline-secondary" href="{{ route('clientes.edit', $case->client) }}">Cadastrar telefone</a>
            @endif
            <a class="btn btn-primary" href="{{ route('casos.edit', $case) }}">Editar caso</a>
        </div>
    </div>

    @error('whatsapp')
        <div class="alert alert-warning" role="alert">{{ $message }}</div>
    @enderror
    @error('delete')
        <div class="alert alert-danger" role="alert">{{ $message }}</div>
    @enderror

    <section class="content-section case-details mb-4">
        <h2 class="h5 mb-3">Informações do caso</h2>
        <dl class="row mb-0">
            <dt class="col-sm-3">Tipo</dt>
            <dd class="col-sm-9">{{ $case->caseType?->name ?: 'Não informado' }}</dd>

            <dt class="col-sm-3">Abertura</dt>
            <dd class="col-sm-9">{{ $case->opened_at?->format('d/m/Y') ?: 'Não informada' }}</dd>

            <dt class="col-sm-3">Encerramento</dt>
            <dd class="col-sm-9">{{ $case->closed_at?->format('d/m/Y') ?: 'Em andamento' }}</dd>
        </dl>
    </section>

    <section class="content-section mb-4">
        <h2 class="h5 mb-3">Descrição</h2>
        <p class="mb-0 text-break">{{ $case->description ?: 'Nenhuma descrição cadastrada.' }}</p>
    </section>

    <section class="danger-zone">
        <div>
            <h2 class="h6 mb-1">Excluir caso</h2>
            <p class="text-secondary small mb-0">Casos com documentos ou checklist não podem ser excluídos.</p>
        </div>
        <form
            method="POST"
            action="{{ route('casos.destroy', $case) }}"
            onsubmit="return confirm('Tem certeza de que deseja excluir este caso?')"
        >
            @csrf
            @method('DELETE')
            <button class="btn btn-outline-danger" type="submit">Excluir</button>
        </form>
    </section>
@endsection
