@extends('layouts.app')

@section('title', 'Contratos | ClienteJus')

@section('content')
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
        <div>
            <p class="text-uppercase text-primary fw-semibold small mb-2">Gestão contratual</p>
            <h1 class="h3 mb-1">Contratos</h1>
            <p class="text-secondary mb-0">Acompanhe vigência, vencimento e documento original.</p>
        </div>
        <a class="btn btn-primary" href="{{ route('contratos.create') }}">Novo contrato</a>
    </div>

    <form class="case-filters mb-4" method="GET" action="{{ route('contratos.index') }}">
        <input
            class="form-control"
            name="search"
            type="search"
            value="{{ $search }}"
            placeholder="Buscar por contrato, caso ou cliente"
            aria-label="Buscar contratos"
        >
        <select class="form-select" name="status" aria-label="Filtrar contratos por status">
            <option value="">Todos os status</option>
            @foreach ($statuses as $value => $label)
                <option value="{{ $value }}" @selected($selectedStatus === $value)>{{ $label }}</option>
            @endforeach
        </select>
        <button class="btn btn-outline-primary" type="submit">Filtrar</button>
        @if ($search !== '' || $selectedStatus !== '')
            <a class="small" href="{{ route('contratos.index') }}">Limpar</a>
        @endif
    </form>

    @if ($contracts->isEmpty())
        <div class="empty-message">
            <p class="mb-1 fw-semibold">Nenhum contrato encontrado.</p>
            <p class="text-secondary mb-0">Cadastre o contrato original para iniciar o controle de vigência.</p>
        </div>
    @else
        <div class="table-responsive border rounded-2 bg-white">
            <table class="table align-middle mb-0">
                <thead>
                    <tr>
                        <th scope="col">Contrato</th>
                        <th scope="col">Cliente</th>
                        <th scope="col">Vencimento</th>
                        <th scope="col">Status</th>
                        <th scope="col" class="text-end">Ações</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($contracts as $contract)
                        <tr>
                            <td>
                                <div class="fw-semibold">{{ $contract->title }}</div>
                                <small class="text-secondary">{{ $contract->legalCase->title }}</small>
                            </td>
                            <td>{{ $contract->legalCase->client->name }}</td>
                            <td>
                                {{ $contract->expires_at?->format('d/m/Y') ?: 'Sem vencimento' }}
                                @if ($contract->isExpiringSoon())
                                    <div><span class="badge text-bg-warning mt-1">Vence em breve</span></div>
                                @elseif ($contract->isPastDue())
                                    <div><span class="badge text-bg-danger mt-1">Prazo vencido</span></div>
                                @endif
                            </td>
                            <td><x-contract-status-badge :status="$contract->status" :label="$contract->statusLabel()" /></td>
                            <td class="text-end text-nowrap">
                                <a class="btn btn-sm btn-outline-primary" href="{{ route('contratos.show', $contract) }}">Ver</a>
                                <a class="btn btn-sm btn-outline-secondary" href="{{ route('contratos.edit', $contract) }}">Editar</a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="mt-3">{{ $contracts->links() }}</div>
    @endif
@endsection
