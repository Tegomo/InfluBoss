{{-- Champs communs aux formulaires de création et de modification d'un contrat. --}}
<div class="row">
    <div class="col-md-6">
        <div class="form-group">
            <label for="influencer_id">Ambassadeur</label>
            <select name="influencer_id" id="influencer_id" class="form-control select2 @error('influencer_id') is-invalid @enderror" required style="width: 100%">
                <option value="">— Choisir —</option>
                @foreach($influencers as $influencer)
                    <option value="{{ $influencer->id }}" {{ (string) old('influencer_id', $contract->influencer_id) === (string) $influencer->id ? 'selected' : '' }}>{{ $influencer->name }}{{ $influencer->pseudo ? ' (@' . $influencer->pseudo . ')' : '' }}</option>
                @endforeach
            </select>
            @error('influencer_id')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
        </div>
    </div>
    <div class="col-md-3">
        <div class="form-group">
            <label for="type">Type de contrat</label>
            <select name="type" id="type" class="form-control @error('type') is-invalid @enderror" required>
                @foreach($types as $key => $label)
                    <option value="{{ $key }}" {{ old('type', $contract->type) === $key ? 'selected' : '' }}>{{ $label }}</option>
                @endforeach
            </select>
            @error('type')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
    </div>
    <div class="col-md-3">
        <div class="form-group">
            <label for="status">Statut</label>
            <select name="status" id="status" class="form-control @error('status') is-invalid @enderror" required>
                @foreach($statuses as $key => $status)
                    <option value="{{ $key }}" {{ old('status', $contract->status) === $key ? 'selected' : '' }}>{{ $status['label'] }}</option>
                @endforeach
            </select>
            @error('status')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-3">
        <div class="form-group">
            <label for="start_date">Date de début</label>
            <input type="date" name="start_date" id="start_date" class="form-control @error('start_date') is-invalid @enderror" value="{{ old('start_date', $contract->start_date?->format('Y-m-d')) }}" required>
            @error('start_date')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
    </div>
    <div class="col-md-3">
        <div class="form-group">
            <label for="end_date">Date de fin</label>
            <input type="date" name="end_date" id="end_date" class="form-control @error('end_date') is-invalid @enderror" value="{{ old('end_date', $contract->end_date?->format('Y-m-d')) }}" required>
            @error('end_date')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
    </div>
    <div class="col-md-6">
        <div class="form-group">
            <label for="amount">Montant de la rémunération (FCFA)</label>
            <input type="number" min="0" step="1" name="amount" id="amount" class="form-control @error('amount') is-invalid @enderror" value="{{ old('amount', $contract->amount !== null ? (int) $contract->amount : '') }}" placeholder="Ex. 1500000">
            @error('amount')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
    </div>
</div>

<div class="form-group">
    <label for="payment_terms">Modalités de paiement</label>
    <textarea name="payment_terms" id="payment_terms" rows="2" class="form-control @error('payment_terms') is-invalid @enderror" placeholder="Ex. 50 % à la signature, 50 % à la fin ; virement mensuel ; Mobile Money…">{{ old('payment_terms', $contract->payment_terms) }}</textarea>
    @error('payment_terms')<div class="invalid-feedback">{{ $message }}</div>@enderror
</div>

<div class="form-group">
    <label for="file">Contrat scanné <small class="text-muted">(PDF, JPG ou PNG, 10 Mo maximum)</small></label>
    <div class="custom-file">
        <input type="file" name="file" id="file" class="custom-file-input @error('file') is-invalid @enderror" accept=".pdf,.jpg,.jpeg,.png">
        <label class="custom-file-label" for="file">{{ $contract->file_original_name ? 'Remplacer : ' . $contract->file_original_name : 'Choisir un fichier' }}</label>
    </div>
    @error('file')<div class="text-danger small">{{ $message }}</div>@enderror
    @if($contract->exists && $contract->file_path)
        <small class="form-text text-muted">Fichier actuel : <a href="{{ route('contracts.download', $contract) }}">{{ $contract->file_original_name }}</a>. Un nouveau fichier le remplacera.</small>
    @endif
</div>

<div class="form-group">
    <label for="notes">Notes internes</label>
    <textarea name="notes" id="notes" rows="2" class="form-control @error('notes') is-invalid @enderror">{{ old('notes', $contract->notes) }}</textarea>
    @error('notes')<div class="invalid-feedback">{{ $message }}</div>@enderror
</div>

@push('scripts')
<script>
    $(function () {
        $('#file').on('change', function () {
            if (this.files[0]) $(this).next('.custom-file-label').text(this.files[0].name);
        });
    });
</script>
@endpush
