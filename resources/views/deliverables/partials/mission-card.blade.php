{{-- Livrables d'une mission (page mission). $assignment et $deliverables (avec withProgress) requis. --}}
<div class="card card-outline card-primary" id="deliverables">
    <div class="card-header">
        <h3 class="card-title"><i class="fas fa-clipboard-check mr-1"></i> Livrables attendus</h3>
        <div class="card-tools">
            @php
                $validatedCount = $deliverables->where('status', \App\Models\Deliverable::STATUS_VALIDATED)->count();
            @endphp
            @if($deliverables->isNotEmpty())
                <span class="badge badge-success">{{ $validatedCount }} / {{ $deliverables->count() }} validé(s)</span>
            @endif
        </div>
    </div>
    <div class="card-body p-0">
        @if($deliverables->isNotEmpty())
        <div class="table-responsive">
            <table class="table table-sm table-hover mb-0">
                <thead>
                    <tr>
                        <th>Livrable</th>
                        <th>Plateforme</th>
                        <th>Date limite</th>
                        <th title="vérifiées / soumises / attendues">Publications</th>
                        <th>Statut</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($deliverables as $deliverable)
                    <tr>
                        <td>
                            <strong>{{ $deliverable->quantity }} × {{ $deliverable->type_label }}</strong>
                            @if($deliverable->description)
                                <br><small class="text-muted">{{ $deliverable->description }}</small>
                            @endif
                        </td>
                        <td>
                            @if($deliverable->platform)
                                @php $cfg = config('influboss.platforms.' . $deliverable->platform); @endphp
                                <i class="{{ $cfg['icon'] ?? 'fas fa-link' }}" style="color: {{ $cfg['color'] ?? '#6c757d' }}"></i> {{ $deliverable->platform_label }}
                            @else
                                <span class="text-muted">Indifférente</span>
                            @endif
                        </td>
                        <td>
                            {{ $deliverable->due_date ? $deliverable->due_date->format('d/m/Y') : '—' }}
                            @if($deliverable->is_overdue)
                                <span class="badge badge-danger">En retard</span>
                            @endif
                        </td>
                        <td>{{ (int) $deliverable->verified_count }} / {{ (int) $deliverable->submitted_count }} / {{ $deliverable->quantity }}</td>
                        <td><span class="badge badge-{{ $deliverable->status_badge }}">{{ $deliverable->status_label }}</span></td>
                        <td class="text-right text-nowrap">
                            @if($deliverable->status !== \App\Models\Deliverable::STATUS_VALIDATED && in_array($assignment->status, ['accepted', 'in_progress']) && (auth()->id() === $assignment->influencer_id || auth()->user()->can('update', $deliverable)))
                            <a href="{{ route('publications.create', ['assignment_id' => $assignment->id, 'deliverable_id' => $deliverable->id]) }}" class="btn btn-xs btn-success" title="Déclarer une publication pour ce livrable"><i class="fas fa-paper-plane"></i> Déclarer</a>
                            @endif
                            @can('update', $deliverable)
                            <a href="{{ route('deliverables.edit', $deliverable) }}" class="btn btn-xs btn-primary" title="Modifier"><i class="fas fa-edit"></i></a>
                            @endcan
                            @can('delete', $deliverable)
                            <form action="{{ route('deliverables.destroy', $deliverable) }}" method="POST" class="d-inline" onsubmit="return confirm('Supprimer ce livrable ? Les publications rattachées seront conservées.')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-xs btn-outline-danger" title="Supprimer"><i class="fas fa-trash"></i></button>
                            </form>
                            @endcan
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @else
            <p class="text-muted p-3 mb-0">Aucun livrable défini pour cette mission.</p>
        @endif
    </div>
    @can('create', [\App\Models\Deliverable::class, $assignment])
    <div class="card-footer">
        <form method="POST" action="{{ route('assignments.deliverables.store', $assignment) }}">
            @csrf
            <p class="mb-2"><b>Ajouter un livrable</b></p>
            @include('deliverables.partials.fields', [
                'deliverable' => new \App\Models\Deliverable(),
                'types' => config('influboss.deliverable_types'),
                'platforms' => config('influboss.platforms'),
                'prefix' => 'new',
            ])
            <button type="submit" class="btn btn-sm btn-primary"><i class="fas fa-plus mr-1"></i> Ajouter</button>
            <small class="text-muted ml-2">Le nombre de publications requises de la mission suit la somme des quantités.</small>
        </form>
    </div>
    @endcan
</div>
