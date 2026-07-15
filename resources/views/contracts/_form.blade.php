@php($contract = $contract ?? null)

<div class="row g-3">
    <div class="col-12">
        <label class="form-label" for="legal_case_id">Caso vinculado</label>
        <select class="form-select @error('legal_case_id') is-invalid @enderror" id="legal_case_id" name="legal_case_id" required>
            <option value="">Selecione</option>
            @foreach ($cases as $caseOption)
                <option
                    value="{{ $caseOption->id }}"
                    @selected((int) old('legal_case_id', $contract?->legal_case_id ?? $selectedCaseId ?? 0) === $caseOption->id)
                >
                    {{ $caseOption->title }} — {{ $caseOption->client->name }}
                </option>
            @endforeach
        </select>
        @error('legal_case_id')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="col-12">
        <label class="form-label" for="title">Título do contrato</label>
        <input
            class="form-control @error('title') is-invalid @enderror"
            id="title"
            name="title"
            type="text"
            value="{{ old('title', $contract?->title) }}"
            maxlength="255"
            autofocus
            required
        >
        @error('title')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="col-12 col-md-4">
        <label class="form-label" for="signed_at">Data de assinatura</label>
        <input
            class="form-control @error('signed_at') is-invalid @enderror"
            id="signed_at"
            name="signed_at"
            type="date"
            value="{{ old('signed_at', $contract?->signed_at?->format('Y-m-d') ?? now()->format('Y-m-d')) }}"
            required
        >
        @error('signed_at')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="col-12 col-md-4">
        <label class="form-label" for="expires_at">Data de vencimento</label>
        <input
            class="form-control @error('expires_at') is-invalid @enderror"
            id="expires_at"
            name="expires_at"
            type="date"
            value="{{ old('expires_at', $contract?->expires_at?->format('Y-m-d')) }}"
        >
        @error('expires_at')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="col-12 col-md-4">
        <label class="form-label" for="status">Status</label>
        <select class="form-select @error('status') is-invalid @enderror" id="status" name="status" required>
            @foreach ($statuses as $value => $label)
                <option value="{{ $value }}" @selected(old('status', $contract?->status ?? 'active') === $value)>
                    {{ $label }}
                </option>
            @endforeach
        </select>
        @error('status')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="col-12">
        <label class="form-label" for="original_document">Documento original</label>
        <input
            class="form-control @error('original_document') is-invalid @enderror"
            id="original_document"
            name="original_document"
            type="file"
            accept=".pdf,.doc,.docx"
        >
        <div class="form-text">
            PDF ou Word, com até 10 MB.
            @if ($contract?->original_document_name)
                O envio de um novo arquivo substituirá {{ $contract->original_document_name }}.
            @endif
        </div>
        @error('original_document')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>
</div>
