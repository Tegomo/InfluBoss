@extends('layouts.adminlte')

@section('title', 'Contrat #' . $contract->id)

@section('breadcrumb')
<li class="breadcrumb-item"><a href="{{ route('contracts.index') }}">Contrats</a></li>
<li class="breadcrumb-item active">Contrat #{{ $contract->id }}</li>
@endsection

@section('content')
@if(session('success'))
<div class="alert alert-success alert-dismissible">
    <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
    {{ session('success') }}
</div>
@endif

<div class="row">
    <div class="col-md-8">
        <div class="card card-primary card-outline">
            <div class="card-header">
                <h3 class="card-title">
                    Contrat {{ strtolower($contract->type_label) }} —
                    @if($contract->influencer && auth()->user()->role !== 'influencer')
                        <a href="{{ route('influencers.show', $contract->influencer) }}">{{ $contract->influencer->name }}</a>
                    @else
                        {{ $contract->influencer?->name }}
                    @endif
                </h3>
                <div class="card-tools">
                    <span class="badge badge-{{ $contract->status_badge }}">{{ $contract->status_label }}</span>
                    @include('contracts.partials.remaining-badge')
                </div>
            </div>
            <div class="card-body">
                <dl class="row mb-0">
                    <dt class="col-sm-4">Type</dt>
                    <dd class="col-sm-8">{{ $contract->type_label }}</dd>

                    <dt class="col-sm-4">Période</dt>
                    <dd class="col-sm-8">du {{ $contract->start_date->format('d/m/Y') }} au {{ $contract->end_date->format('d/m/Y') }}</dd>

                    <dt class="col-sm-4">Montant de la rémunération</dt>
                    <dd class="col-sm-8">{{ $contract->amount !== null ? number_format((float) $contract->amount, 0, ',', ' ') . ' FCFA' : 'Non renseigné' }}</dd>

                    <dt class="col-sm-4">Modalités de paiement</dt>
                    <dd class="col-sm-8">{!! $contract->payment_terms ? nl2br(e($contract->payment_terms)) : '<span class="text-muted">Non renseignées</span>' !!}</dd>

                    <dt class="col-sm-4">Document contractuel</dt>
                    <dd class="col-sm-8">
                        @if($contract->file_path)
                            <a href="{{ route('contracts.download', $contract) }}"><i class="fas fa-file-download mr-1"></i>{{ $contract->file_original_name }}</a>
                        @else
                            <span class="text-muted">Aucun document joint</span>
                        @endif
                    </dd>

                    @if(auth()->user()->role !== 'influencer')
                    <dt class="col-sm-4">Notes internes</dt>
                    <dd class="col-sm-8">{!! $contract->notes ? nl2br(e($contract->notes)) : '<span class="text-muted">—</span>' !!}</dd>
                    @endif
                </dl>
            </div>
            <div class="card-footer">
                @can('update', $contract)
                <a href="{{ route('contracts.edit', $contract) }}" class="btn btn-primary"><i class="fas fa-edit mr-1"></i> Modifier</a>
                @endcan
                @can('delete', $contract)
                <form action="{{ route('contracts.destroy', $contract) }}" method="POST" class="d-inline" onsubmit="return confirm('Supprimer ce contrat ?')">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-outline-danger"><i class="fas fa-trash mr-1"></i> Supprimer</button>
                </form>
                @endcan
                <a href="{{ route('contracts.index') }}" class="btn btn-default">Retour à la liste</a>
            </div>
        </div>
    </div>

    <div class="col-md-4">
        <div class="card">
            <div class="card-header"><h3 class="card-title">Alertes d'expiration</h3></div>
            <div class="card-body">
                <p class="small text-muted">Alertes envoyées aux administrateurs et aux superviseurs de l'ambassadeur {{ implode(', ', array_map(fn ($d) => $d . ' j', config('influboss.contract_alert_days'))) }} avant la fin d'un contrat actif.</p>
                @forelse($contract->alerts->sortByDesc('days_before') as $alert)
                    <div><i class="fas fa-bell text-warning mr-1"></i> Seuil {{ $alert->days_before }} jours — traité le {{ $alert->sent_at->format('d/m/Y') }}</div>
                @empty
                    <p class="mb-0 text-muted">Aucune alerte envoyée pour ce contrat.</p>
                @endforelse
            </div>
        </div>
        <div class="card">
            <div class="card-body small text-muted">
                Créé le {{ $contract->created_at->format('d/m/Y') }}{{ $contract->creator ? ' par ' . $contract->creator->name : '' }}.<br>
                Dernière modification le {{ $contract->updated_at->format('d/m/Y') }}.
            </div>
        </div>
    </div>
</div>
@endsection
