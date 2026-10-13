{{--
    Choix du livrable auquel répond la publication. Les options de toutes les missions proposées sont rendues,
    puis filtrées en JavaScript selon la mission sélectionnée dans #assignment_id.
    $assignments (avec deliverables chargés) et $selectedDeliverableId requis.
--}}
<div class="form-group">
    <label for="deliverable_id">Livrable concerné <small class="text-muted">(optionnel)</small></label>
    <select class="form-control @error('deliverable_id') is-invalid @enderror" id="deliverable_id" name="deliverable_id">
        <option value="">Aucun livrable précis</option>
        @foreach($assignments as $missionOption)
            @foreach($missionOption->deliverables as $deliverableOption)
                <option value="{{ $deliverableOption->id }}"
                        data-assignment-id="{{ $missionOption->id }}"
                        {{ (string) $selectedDeliverableId === (string) $deliverableOption->id ? 'selected' : '' }}>
                    {{ $deliverableOption->quantity }} × {{ $deliverableOption->type_label }}{{ $deliverableOption->platform_label ? ' ' . $deliverableOption->platform_label : '' }}{{ $deliverableOption->due_date ? ' — avant le ' . $deliverableOption->due_date->format('d/m/Y') : '' }} ({{ $deliverableOption->status_label }})
                </option>
            @endforeach
        @endforeach
    </select>
    @error('deliverable_id')
        <span class="invalid-feedback d-block">{{ $message }}</span>
    @enderror
    <small class="form-text text-muted">Le statut du livrable se met à jour automatiquement avec cette publication.</small>
</div>

@if($filterByMission ?? true)
@push('scripts')
<script>
    $(function () {
        const $mission = $('#assignment_id');
        const $deliverable = $('#deliverable_id');
        if (!$mission.length || !$deliverable.length) return;

        function filterDeliverables() {
            const missionId = String($mission.val() || '');
            let visible = 0;
            $deliverable.find('option[data-assignment-id]').each(function () {
                const match = String($(this).data('assignment-id')) === missionId;
                $(this).prop('hidden', !match).prop('disabled', !match);
                if (match) visible++;
            });
            const $selected = $deliverable.find('option:selected');
            if ($selected.prop('disabled')) $deliverable.val('');
            $deliverable.closest('.form-group').toggle(visible > 0);
        }

        $mission.on('change', filterDeliverables);
        filterDeliverables();
    });
</script>
@endpush
@endif
