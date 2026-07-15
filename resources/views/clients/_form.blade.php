@php($client = $client ?? null)

<div class="row g-3">
    <div class="col-12">
        <label class="form-label" for="name">Nome completo</label>
        <input
            class="form-control @error('name') is-invalid @enderror"
            id="name"
            name="name"
            type="text"
            value="{{ old('name', $client?->name) }}"
            maxlength="255"
            autofocus
            required
        >
        @error('name')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="col-12 col-md-6">
        <label class="form-label" for="email">E-mail</label>
        <input
            class="form-control @error('email') is-invalid @enderror"
            id="email"
            name="email"
            type="email"
            value="{{ old('email', $client?->email) }}"
            maxlength="255"
        >
        @error('email')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="col-12 col-md-6">
        <label class="form-label" for="phone">Telefone</label>
        <input
            class="form-control @error('phone') is-invalid @enderror"
            id="phone"
            name="phone"
            type="tel"
            value="{{ old('phone', $client?->phone) }}"
            maxlength="30"
            placeholder="(00) 00000-0000"
        >
        @error('phone')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="col-12 col-md-6">
        <label class="form-label" for="document">CPF/CNPJ</label>
        <input
            class="form-control @error('document') is-invalid @enderror"
            id="document"
            name="document"
            type="text"
            value="{{ old('document', $client?->document) }}"
            maxlength="30"
        >
        @error('document')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="col-12">
        <label class="form-label" for="notes">Observações</label>
        <textarea
            class="form-control @error('notes') is-invalid @enderror"
            id="notes"
            name="notes"
            rows="5"
            maxlength="5000"
        >{{ old('notes', $client?->notes) }}</textarea>
        @error('notes')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>
</div>
