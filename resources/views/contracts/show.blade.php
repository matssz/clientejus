@extends('layouts.app')

@section('title', $contract->title . ' | ClienteJus')

@section('content')
    <a class="d-inline-block mb-3" href="{{ route('contratos.index') }}">Voltar para contratos</a>

    @if ($contract->isPastDue() && $contract->status === 'active')
        <div class="alert alert-danger" role="alert">
            Este contrato ultrapassou a data de vencimento. Revise o status ou registre a renovação.
        </div>
    @elseif ($contract->isExpiringSoon())
        <div class="alert alert-warning" role="alert">
            Vencimento previsto {{ $contract->expires_at->diffForHumans() }}.
        </div>
    @endif

    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-start gap-3 mb-4">
        <div>
            <div class="d-flex flex-wrap align-items-center gap-2 mb-2">
                <p class="text-uppercase text-primary fw-semibold small mb-0">Contrato</p>
                <x-contract-status-badge :status="$contract->status" :label="$contract->statusLabel()" />
            </div>
            <h1 class="h3 mb-1">{{ $contract->title }}</h1>
            <p class="text-secondary mb-0">
                Caso: <a href="{{ route('casos.show', $contract->legalCase) }}">{{ $contract->legalCase->title }}</a>
            </p>
        </div>
        <a class="btn btn-primary" href="{{ route('contratos.edit', $contract) }}">Editar contrato</a>
    </div>

    <section class="content-section mb-4">
        <h2 class="h5 mb-3">Informações contratuais</h2>
        <dl class="row mb-0">
            <dt class="col-sm-3">Cliente</dt>
            <dd class="col-sm-9">
                <a href="{{ route('clientes.show', $contract->legalCase->client) }}">
                    {{ $contract->legalCase->client->name }}
                </a>
            </dd>

            <dt class="col-sm-3">Assinatura</dt>
            <dd class="col-sm-9">{{ $contract->signed_at->format('d/m/Y') }}</dd>

            <dt class="col-sm-3">Vencimento</dt>
            <dd class="col-sm-9">{{ $contract->expires_at?->format('d/m/Y') ?: 'Sem vencimento definido' }}</dd>

            <dt class="col-sm-3">Documento original</dt>
            <dd class="col-sm-9">
                @if ($contract->original_document_path)
                    <a href="{{ route('contratos.download', $contract) }}">
                        {{ $contract->original_document_name ?: 'Baixar documento' }}
                    </a>
                @else
                    <span class="text-secondary">Não enviado</span>
                @endif
            </dd>
        </dl>
    </section>

    <section class="danger-zone">
        <div>
            <h2 class="h6 mb-1">Excluir contrato</h2>
            <p class="text-secondary small mb-0">O documento original armazenado também será removido.</p>
        </div>
        <form
            method="POST"
            action="{{ route('contratos.destroy', $contract) }}"
            onsubmit="return confirm('Tem certeza de que deseja excluir este contrato?')"
        >
            @csrf
            @method('DELETE')
            <button class="btn btn-outline-danger" type="submit">Excluir</button>
        </form>
    </section>
@endsection
