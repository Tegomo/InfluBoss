{{-- Champs d'un livrable. $deliverable (peut être neuf), $types, $platforms, $prefix pour des id uniques. --}}
@php $prefix = $prefix ?? 'deliverable'; @endphp
<div class="form-row">
    <div class="form-group col-md-3">
        <label for="{{ $prefix }}_type" class="small">Type</label>
        <select name="type" id="{{ $prefix }}_type" class="form-control form-control-sm @error('type') is-invalid @enderror" required>
            @foreach($types as $key => $label)
                <option value="{{ $key }}" {{ old('type', $deliverable->type) === $key ? 'selected' : '' }}>{{ $label }}</option>
            @endforeach
        </select>
        @error('type')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
    <div class="form-group col-md-3">
        <label for="{{ $prefix }}_platform" class="small">Plateforme</label>
        <select name="platform" id="{{ $prefix }}_platform" class="form-control form-control-sm @error('platform') is-invalid @enderror">
            <option value="">Indifférente</option>
            @foreach($platforms as $key => $cfg)
                <option value="{{ $key }}" {{ old('platform', $deliverable->platform) === $key ? 'selected' : '' }}>{{ $cfg['label'] }}</option>
            @endforeach
        </select>
        @error('platform')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
    <div class="form-group col-md-2">
        <label for="{{ $prefix }}_quantity" class="small">Quantité</label>
        <input type="number" min="1" max="100" name="quantity" id="{{ $prefix }}_quantity" class="form-control form-control-sm @error('quantity') is-invalid @enderror" value="{{ old('quantity', $deliverable->quantity ?? 1) }}" required>
        @error('quantity')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
    <div class="form-group col-md-4">
        <label for="{{ $prefix }}_due_date" class="small">Date limite</label>
        <input type="date" name="due_date" id="{{ $prefix }}_due_date" class="form-control form-control-sm @error('due_date') is-invalid @enderror" value="{{ old('due_date', $deliverable->due_date?->format('Y-m-d')) }}">
        @error('due_date')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
</div>
<div class="form-group">
    <label for="{{ $prefix }}_description" class="small">Précisions</label>
    <input type="text" name="description" id="{{ $prefix }}_description" class="form-control form-control-sm @error('description') is-invalid @enderror" placeholder="Ex. avec lien en story, format 9:16, voix off…" value="{{ old('description', $deliverable->description) }}">
    @error('description')<div class="invalid-feedback">{{ $message }}</div>@enderror
</div>
