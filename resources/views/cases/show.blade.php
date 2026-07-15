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
    @error('documents_whatsapp')
        <div class="alert alert-warning" role="alert">{{ $message }}</div>
    @enderror
    @error('document')
        <div class="alert alert-danger" role="alert">{{ $message }}</div>
    @enderror
    @error('checklist_item_id')
        <div class="alert alert-danger" role="alert">{{ $message }}</div>
    @enderror
    @error('name')
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

    <section class="content-section mb-4">
        <div class="d-flex justify-content-between align-items-center gap-3 mb-3">
            <h2 class="h5 mb-0">Contratos vinculados</h2>
            <a href="{{ route('contratos.create', ['case_id' => $case->id]) }}">Novo contrato</a>
        </div>

        @if ($case->contracts->isEmpty())
            <p class="text-secondary mb-0">Nenhum contrato cadastrado para este caso.</p>
        @else
            <div class="table-responsive border rounded-2 bg-white">
                <table class="table align-middle mb-0">
                    <thead>
                        <tr>
                            <th scope="col">Contrato</th>
                            <th scope="col">Vencimento</th>
                            <th scope="col">Status</th>
                            <th scope="col" class="text-end">Ação</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($case->contracts as $contract)
                            <tr>
                                <td class="fw-semibold">{{ $contract->title }}</td>
                                <td>{{ $contract->expires_at?->format('d/m/Y') ?: 'Sem vencimento' }}</td>
                                <td><x-contract-status-badge :status="$contract->status" :label="$contract->statusLabel()" /></td>
                                <td class="text-end">
                                    <a class="btn btn-sm btn-outline-primary" href="{{ route('contratos.show', $contract) }}">Ver</a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </section>

    <section class="content-section mb-4" id="documentos">
        <div class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-start gap-3 mb-3">
            <div>
                <h2 class="h5 mb-1">Documentos do caso</h2>
                <p class="text-secondary mb-0">
                    {{ $completedItemCount }} de {{ $requiredItemCount }} documentos obrigatórios concluídos.
                </p>
            </div>

            @if ($requiredItemCount > $completedItemCount)
                @if ($case->client->phone)
                    <a
                        class="btn btn-success"
                        href="{{ route('casos.documents.whatsapp', $case) }}"
                        target="_blank"
                        rel="noopener noreferrer"
                    >Cobrar pendências</a>
                @else
                    <a class="btn btn-outline-secondary" href="{{ route('clientes.edit', $case->client) }}">Cadastrar telefone</a>
                @endif
            @endif
        </div>

        <div
            class="progress mb-4"
            role="progressbar"
            aria-label="Progresso dos documentos"
            aria-valuenow="{{ $documentProgress }}"
            aria-valuemin="0"
            aria-valuemax="100"
        >
            <div class="progress-bar" style="width: {{ $documentProgress }}%">{{ $documentProgress }}%</div>
        </div>

        @if ($case->checklistItems->isEmpty())
            <div class="empty-message mb-4">
                <p class="mb-3">Este caso ainda não possui checklist.</p>
                <form method="POST" action="{{ route('casos.checklist.generate', $case) }}">
                    @csrf
                    <button class="btn btn-primary" type="submit">Gerar checklist padrão</button>
                </form>
            </div>
        @else
            <div class="checklist-list mb-4">
                @foreach ($case->checklistItems->sortBy('name') as $item)
                    <div class="checklist-row">
                        <div class="checklist-heading">
                            <form method="POST" action="{{ route('casos.checklist.update', [$case, $item]) }}">
                                @csrf
                                @method('PATCH')
                                <input name="is_completed" type="hidden" value="0">
                                <input
                                    class="form-check-input checklist-checkbox"
                                    name="is_completed"
                                    type="checkbox"
                                    value="1"
                                    aria-label="Marcar {{ $item->name }} como concluído"
                                    @checked($item->is_completed)
                                    onchange="this.form.submit()"
                                >
                            </form>

                            <div class="min-width-0">
                                <p class="fw-semibold mb-1 {{ $item->is_completed ? 'text-decoration-line-through text-secondary' : '' }}">
                                    {{ $item->name }}
                                </p>
                                <div class="d-flex flex-wrap gap-2">
                                    @if ($item->is_required)
                                        <span class="badge text-bg-light border text-secondary">Obrigatório</span>
                                    @endif
                                    <span class="small {{ $item->is_completed ? 'text-success' : 'text-secondary' }}">
                                        {{ $item->is_completed ? 'Recebido' : 'Pendente' }}
                                    </span>
                                </div>
                            </div>

                            <form
                                method="POST"
                                action="{{ route('casos.checklist.destroy', [$case, $item]) }}"
                                onsubmit="return confirm('Remover este item do checklist?')"
                            >
                                @csrf
                                @method('DELETE')
                                <button class="btn btn-sm btn-outline-danger" type="submit">Remover</button>
                            </form>
                        </div>

                        @if ($item->documents->isNotEmpty())
                            <div class="document-links">
                                @foreach ($item->documents as $document)
                                    <div class="document-link-row">
                                        <a href="{{ route('casos.documents.download', [$case, $document]) }}">
                                            {{ $document->original_name }}
                                        </a>
                                        <form
                                            method="POST"
                                            action="{{ route('casos.documents.destroy', [$case, $document]) }}"
                                            onsubmit="return confirm('Excluir este arquivo?')"
                                        >
                                            @csrf
                                            @method('DELETE')
                                            <button class="btn btn-sm btn-link text-danger p-0" type="submit">Excluir arquivo</button>
                                        </form>
                                    </div>
                                @endforeach
                            </div>
                        @endif

                        <form
                            class="document-upload"
                            method="POST"
                            action="{{ route('casos.documents.store', $case) }}"
                            enctype="multipart/form-data"
                        >
                            @csrf
                            <input name="checklist_item_id" type="hidden" value="{{ $item->id }}">
                            <input
                                class="form-control form-control-sm"
                                name="document"
                                type="file"
                                accept=".pdf,.doc,.docx,.jpg,.jpeg,.png"
                                aria-label="Selecionar arquivo para {{ $item->name }}"
                                required
                            >
                            <button class="btn btn-sm btn-outline-primary" type="submit">Enviar</button>
                        </form>
                    </div>
                @endforeach
            </div>
        @endif

        <div class="document-tools">
            <form class="document-tool" method="POST" action="{{ route('casos.checklist.store', $case) }}">
                @csrf
                <label class="form-label fw-semibold" for="checklist-name">Adicionar pendência</label>
                <div class="input-group">
                    <input class="form-control" id="checklist-name" name="name" type="text" maxlength="255" required>
                    <button class="btn btn-outline-primary" type="submit">Adicionar</button>
                </div>
                <div class="form-check mt-2">
                    <input class="form-check-input" id="is-required" name="is_required" type="checkbox" value="1" checked>
                    <label class="form-check-label" for="is-required">Documento obrigatório</label>
                </div>
            </form>

            <form
                class="document-tool"
                method="POST"
                action="{{ route('casos.documents.store', $case) }}"
                enctype="multipart/form-data"
            >
                @csrf
                <label class="form-label fw-semibold" for="standalone-document">Enviar arquivo avulso</label>
                <div class="input-group">
                    <input
                        class="form-control"
                        id="standalone-document"
                        name="document"
                        type="file"
                        accept=".pdf,.doc,.docx,.jpg,.jpeg,.png"
                        required
                    >
                    <button class="btn btn-outline-primary" type="submit">Enviar</button>
                </div>
                <div class="form-text">PDF, Word ou imagem, com até 10 MB.</div>
            </form>
        </div>

        @php($standaloneDocuments = $case->documents->whereNull('checklist_item_id'))
        @if ($standaloneDocuments->isNotEmpty())
            <div class="mt-4">
                <h3 class="h6">Arquivos avulsos</h3>
                <div class="document-links">
                    @foreach ($standaloneDocuments as $document)
                        <div class="document-link-row">
                            <a href="{{ route('casos.documents.download', [$case, $document]) }}">
                                {{ $document->original_name }}
                            </a>
                            <form
                                method="POST"
                                action="{{ route('casos.documents.destroy', [$case, $document]) }}"
                                onsubmit="return confirm('Excluir este arquivo?')"
                            >
                                @csrf
                                @method('DELETE')
                                <button class="btn btn-sm btn-link text-danger p-0" type="submit">Excluir</button>
                            </form>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif
    </section>

    <section class="danger-zone">
        <div>
            <h2 class="h6 mb-1">Excluir caso</h2>
            <p class="text-secondary small mb-0">Casos com documentos armazenados não podem ser excluídos.</p>
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
