@php($case = $case ?? null)

<div class="row g-3">
    <div class="col-12 col-md-6">
        <label class="form-label" for="client_id">Cliente</label>
        <select class="form-select @error('client_id') is-invalid @enderror" id="client_id" name="client_id" required>
            <option value="">Selecione</option>
            @foreach ($clients as $clientOption)
                <option
                    value="{{ $clientOption->id }}"
                    @selected((int) old('client_id', $case?->client_id ?? $selectedClientId ?? 0) === $clientOption->id)
                >{{ $clientOption->name }}</option>
            @endforeach
        </select>
        @error('client_id')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="col-12 col-md-6">
        <label class="form-label" for="case_type_id">Tipo de caso</label>
        <select class="form-select @error('case_type_id') is-invalid @enderror" id="case_type_id" name="case_type_id" required>
            <option value="">Selecione</option>
            @foreach ($caseTypes as $caseType)
                <option value="{{ $caseType->id }}" @selected((int) old('case_type_id', $case?->case_type_id) === $caseType->id)>
                    {{ $caseType->name }}
                </option>
            @endforeach
        </select>
        @error('case_type_id')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="col-12">
        <label class="form-label" for="title">Título do caso</label>
        <input
            class="form-control @error('title') is-invalid @enderror"
            id="title"
            name="title"
            type="text"
            value="{{ old('title', $case?->title) }}"
            maxlength="255"
            autofocus
            required
        >
        @error('title')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="col-12 col-md-6">
        <label class="form-label" for="status">Status</label>
        <select class="form-select @error('status') is-invalid @enderror" id="status" name="status" required>
            @foreach ($statuses as $value => $label)
                <option value="{{ $value }}" @selected(old('status', $case?->status ?? 'novo_atendimento') === $value)>
                    {{ $label }}
                </option>
            @endforeach
        </select>
        @error('status')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="col-12 col-sm-6 col-md-3">
        <label class="form-label" for="opened_at">Data de abertura</label>
        <input
            class="form-control @error('opened_at') is-invalid @enderror"
            id="opened_at"
            name="opened_at"
            type="date"
            value="{{ old('opened_at', $case?->opened_at?->format('Y-m-d') ?? now()->format('Y-m-d')) }}"
        >
        @error('opened_at')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="col-12 col-sm-6 col-md-3">
        <label class="form-label" for="closed_at">Encerramento</label>
        <input
            class="form-control @error('closed_at') is-invalid @enderror"
            id="closed_at"
            name="closed_at"
            type="date"
            value="{{ old('closed_at', $case?->closed_at?->format('Y-m-d')) }}"
        >
        @error('closed_at')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="col-12">
        <label class="form-label" for="description">Descrição</label>
        <textarea
            class="form-control @error('description') is-invalid @enderror"
            id="description"
            name="description"
            rows="6"
            maxlength="10000"
        >{{ old('description', $case?->description) }}</textarea>
        @error('description')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>
</div>
