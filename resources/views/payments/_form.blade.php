{{-- Champs d'un paiement. $payment, $influencers, $contracts, $assignments, $statuses, $methods requis. --}}
<div class="row">
    <div class="col-md-4">
        <div class="form-group">
            <label for="influencer_id">Ambassadeur</label>
            <select name="influencer_id" id="influencer_id" class="form-control @error('influencer_id') is-invalid @enderror" required>
                <option value="">— Choisir —</option>
                @foreach($influencers as $influencer)
                    <option value="{{ $influencer->id }}" {{ (string) old('influencer_id', $payment->influencer_id) === (string) $influencer->id ? 'selected' : '' }}>{{ $influencer->name }}</option>
                @endforeach
            </select>
            @error('influencer_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
    </div>
    <div class="col-md-4">
        <div class="form-group">
            <label for="contract_id">Contrat <small class="text-muted">(optionnel)</small></label>
            <select name="contract_id" id="contract_id" class="form-control @error('contract_id') is-invalid @enderror">
                <option value="">Aucun</option>
                @foreach($contracts as $contract)
                    <option value="{{ $contract->id }}" data-influencer-id="{{ $contract->influencer_id }}" {{ (string) old('contract_id', $payment->contract_id) === (string) $contract->id ? 'selected' : '' }}>
                        #{{ $contract->id }} {{ $contract->type_label }} ({{ $contract->start_date->format('m/Y') }} – {{ $contract->end_date->format('m/Y') }}){{ $contract->amount !== null ? ' · ' . number_format((float) $contract->amount, 0, ',', ' ') . ' FCFA' : '' }}
                    </option>
                @endforeach
            </select>
            @error('contract_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
    </div>
    <div class="col-md-4">
        <div class="form-group">
            <label for="assignment_id">Mission <small class="text-muted">(optionnel)</small></label>
            <select name="assignment_id" id="assignment_id" class="form-control @error('assignment_id') is-invalid @enderror">
                <option value="">Aucune</option>
                @foreach($assignments as $assignment)
                    <option value="{{ $assignment->id }}" data-influencer-id="{{ $assignment->influencer_id }}" {{ (string) old('assignment_id', $payment->assignment_id) === (string) $assignment->id ? 'selected' : '' }}>
                        Mission #{{ $assignment->id }} — {{ $assignment->campaign?->name ?? 'Campagne supprimée' }}
                    </option>
                @endforeach
            </select>
            @error('assignment_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-6">
        <div class="form-group">
            <label for="label">Libellé</label>
            <input type="text" name="label" id="label" class="form-control @error('label') is-invalid @enderror" placeholder="Ex. Acompte 50 %, solde, prime de performance…" value="{{ old('label', $payment->label) }}" required>
            @error('label')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
    </div>
    <div class="col-md-3">
        <div class="form-group">
            <label for="amount">Montant (FCFA)</label>
            <input type="number" min="1" step="1" name="amount" id="amount" class="form-control @error('amount') is-invalid @enderror" value="{{ old('amount', $payment->amount !== null ? (int) $payment->amount : '') }}" required>
            @error('amount')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
    </div>
    <div class="col-md-3">
        <div class="form-group">
            <label for="due_date">Échéance</label>
            <input type="date" name="due_date" id="due_date" class="form-control @error('due_date') is-invalid @enderror" value="{{ old('due_date', $payment->due_date?->format('Y-m-d')) }}">
            @error('due_date')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-3">
        <div class="form-group">
            <label for="status">Statut</label>
            <select name="status" id="status" class="form-control @error('status') is-invalid @enderror" required>
                @foreach($statuses as $key => $status)
                    <option value="{{ $key }}" {{ old('status', $payment->status) === $key ? 'selected' : '' }}>{{ $status['label'] }}</option>
                @endforeach
            </select>
            @error('status')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
    </div>
    <div class="col-md-3 paid-only">
        <div class="form-group">
            <label for="paid_at">Payé le</label>
            <input type="date" name="paid_at" id="paid_at" class="form-control @error('paid_at') is-invalid @enderror" value="{{ old('paid_at', $payment->paid_at?->format('Y-m-d')) }}">
            @error('paid_at')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
    </div>
    <div class="col-md-3 paid-only">
        <div class="form-group">
            <label for="method">Moyen de paiement</label>
            <select name="method" id="method" class="form-control @error('method') is-invalid @enderror">
                <option value="">—</option>
                @foreach($methods as $key => $label)
                    <option value="{{ $key }}" {{ old('method', $payment->method) === $key ? 'selected' : '' }}>{{ $label }}</option>
                @endforeach
            </select>
            @error('method')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
    </div>
    <div class="col-md-3 paid-only">
        <div class="form-group">
            <label for="reference">Référence de transaction</label>
            <input type="text" name="reference" id="reference" class="form-control @error('reference') is-invalid @enderror" value="{{ old('reference', $payment->reference) }}">
            @error('reference')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
    </div>
</div>

<div class="form-group">
    <label for="receipt">Justificatif <small class="text-muted">(reçu, ordre de virement… PDF, JPG ou PNG, 10 Mo max)</small></label>
    <div class="custom-file">
        <input type="file" name="receipt" id="receipt" class="custom-file-input @error('receipt') is-invalid @enderror" accept=".pdf,.jpg,.jpeg,.png">
        <label class="custom-file-label" for="receipt">{{ $payment->receipt_original_name ? 'Remplacer : ' . $payment->receipt_original_name : 'Choisir un fichier' }}</label>
    </div>
    @error('receipt')<div class="text-danger small">{{ $message }}</div>@enderror
</div>

<div class="form-group">
    <label for="notes">Notes</label>
    <textarea name="notes" id="notes" rows="2" class="form-control @error('notes') is-invalid @enderror">{{ old('notes', $payment->notes) }}</textarea>
    @error('notes')<div class="invalid-feedback">{{ $message }}</div>@enderror
</div>

@push('scripts')
<script>
    $(function () {
        // Contrats et missions proposés selon l'ambassadeur choisi.
        function filterByInfluencer() {
            const influencerId = String($('#influencer_id').val() || '');
            $('#contract_id option[data-influencer-id], #assignment_id option[data-influencer-id]').each(function () {
                const match = influencerId === '' || String($(this).data('influencer-id')) === influencerId;
                $(this).prop('hidden', !match).prop('disabled', !match);
            });
            ['#contract_id', '#assignment_id'].forEach(function (sel) {
                if ($(sel).find('option:selected').prop('disabled')) $(sel).val('');
            });
        }
        // Champs de règlement visibles seulement pour un paiement effectué.
        function togglePaidFields() {
            $('.paid-only').toggle($('#status').val() === 'paid');
        }
        $('#influencer_id').on('change', filterByInfluencer);
        $('#status').on('change', togglePaidFields);
        $('#receipt').on('change', function () {
            if (this.files[0]) $(this).next('.custom-file-label').text(this.files[0].name);
        });
        filterByInfluencer();
        togglePaidFields();
    });
</script>
@endpush
