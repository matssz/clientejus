@extends('layouts.app')

@section('title', 'Casos | ClienteJus')

@section('content')
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
        <div>
            <p class="text-uppercase text-primary fw-semibold small mb-2">Operação jurídica</p>
            <h1 class="h3 mb-1">Casos</h1>
            <p class="text-secondary mb-0">Acompanhe os atendimentos e seus status.</p>
        </div>
        <a class="btn btn-primary" href="{{ route('casos.create') }}">Novo caso</a>
    </div>

    <form class="case-filters mb-4" method="GET" action="{{ route('casos.index') }}">
        <input
            class="form-control"
            name="search"
            type="search"
            value="{{ $search }}"
            placeholder="Buscar por caso ou cliente"
            aria-label="Buscar casos"
        >
        <select class="form-select" name="status" aria-label="Filtrar por status">
            <option value="">Todos os status</option>
            @foreach ($statuses as $value => $label)
                <option value="{{ $value }}" @selected($selectedStatus === $value)>{{ $label }}</option>
            @endforeach
        </select>
        <button class="btn btn-outline-primary" type="submit">Filtrar</button>
        @if ($search !== '' || $selectedStatus !== '')
            <a class="small" href="{{ route('casos.index') }}">Limpar</a>
        @endif
    </form>

    @if ($cases->isEmpty())
        <div class="empty-message">
            <p class="mb-1 fw-semibold">Nenhum caso encontrado.</p>
            <p class="text-secondary mb-0">Cadastre um caso ou ajuste os filtros.</p>
        </div>
    @else
        <div class="table-responsive border rounded-2 bg-white">
            <table class="table align-middle mb-0">
                <thead>
                    <tr>
                        <th scope="col">Caso</th>
                        <th scope="col">Cliente</th>
                        <th scope="col">Tipo</th>
                        <th scope="col">Documentos</th>
                        <th scope="col">Status</th>
                        <th scope="col" class="text-end">Ações</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($cases as $case)
                        <tr>
                            <td>
                                <div class="fw-semibold">{{ $case->title }}</div>
                                <small class="text-secondary">Aberto em {{ $case->opened_at?->format('d/m/Y') ?: 'data não informada' }}</small>
                            </td>
                            <td>{{ $case->client->name }}</td>
                            <td>{{ $case->caseType?->name ?: 'Não informado' }}</td>
                            <td>
                                {{ $case->completed_document_count }}/{{ $case->required_document_count }}
                                <small class="text-secondary">concluídos</small>
                            </td>
                            <td><x-status-badge :status="$case->status" :label="$case->statusLabel()" /></td>
                            <td class="text-end text-nowrap">
                                <a class="btn btn-sm btn-outline-primary" href="{{ route('casos.show', $case) }}">Ver</a>
                                <a class="btn btn-sm btn-outline-secondary" href="{{ route('casos.edit', $case) }}">Editar</a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="mt-3">{{ $cases->links() }}</div>
    @endif
@endsection
