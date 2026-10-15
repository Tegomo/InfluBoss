@extends('layouts.adminlte')

@section('title', 'Contrats')

@section('breadcrumb')
<li class="breadcrumb-item active">Contrats</li>
@endsection

@section('content')
@if(session('success'))
<div class="alert alert-success alert-dismissible">
    <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
    {{ session('success') }}
</div>
@endif

@if($expiringCount > 0 && ! request()->boolean('expiring'))
<div class="callout callout-warning">
    <i class="fas fa-exclamation-triangle mr-1 text-warning"></i>
    <strong>{{ $expiringCount }}</strong> contrat(s) actif(s) arrivent à échéance dans les {{ $alertWindow }} prochains jours.
    <a href="{{ route('contracts.index', ['expiring' => 1]) }}">Les afficher</a>
</div>
@endif

<div class="card">
    <div class="card-header">
        <h3 class="card-title">Liste des contrats</h3>
        <div class="card-tools">
            @can('create', \App\Models\Contract::class)
            <a href="{{ route('contracts.create') }}" class="btn btn-primary btn-sm"><i class="fas fa-plus mr-1"></i> Nouveau contrat</a>
            @endcan
        </div>
    </div>
    <div class="card-body table-responsive p-0">
        <div class="p-3 bg-light">
            <form action="{{ route('contracts.index') }}" method="GET" class="row">
                @if(auth()->user()->role !== 'influencer')
                <div class="col-md-3 mb-2">
                    <input type="text" name="search" class="form-control form-control-sm" placeholder="Ambassadeur…" value="{{ request('search') }}">
                </div>
                @endif
                <div class="col-md-2 mb-2">
                    <select name="status" class="form-control form-control-sm">
                        <option value="">Statut (Tous)</option>
                        @foreach($statuses as $key => $status)
                            <option value="{{ $key }}" {{ request('status') === $key ? 'selected' : '' }}>{{ $status['label'] }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2 mb-2">
                    <select name="type" class="form-control form-control-sm">
                        <option value="">Type (Tous)</option>
                        @foreach($types as $key => $label)
                            <option value="{{ $key }}" {{ request('type') === $key ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2 mb-2">
                    <div class="custom-control custom-checkbox mt-1">
                        <input type="checkbox" class="custom-control-input" id="expiring" name="expiring" value="1" {{ request()->boolean('expiring') ? 'checked' : '' }}>
                        <label class="custom-control-label" for="expiring">Expirant sous {{ $alertWindow }} j</label>
                    </div>
                </div>
                <div class="col-md-3 mb-2">
                    <button type="submit" class="btn btn-sm btn-info mr-1">Filtrer</button>
                    <a href="{{ route('contracts.index') }}" class="btn btn-sm btn-secondary">Réinitialiser</a>
                </div>
            </form>
        </div>

        <table class="table table-hover text-nowrap">
            <thead>
                <tr>
                    <th>Ambassadeur</th>
                    <th>Type</th>
                    <th>Période</th>
                    <th class="text-right">Montant (FCFA)</th>
                    <th>Statut</th>
                    <th>Document</th>
                    <th class="text-right">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($contracts as $contract)
                <tr>
                    <td>
                        @if($contract->influencer)
                            @if(auth()->user()->role === 'influencer')
                                {{ $contract->influencer->name }}
                            @else
                                <a href="{{ route('influencers.show', $contract->influencer) }}">{{ $contract->influencer->name }}</a>
                            @endif
                        @else
                            <span class="text-muted">—</span>
                        @endif
                    </td>
                    <td>{{ $contract->type_label }}</td>
                    <td>
                        {{ $contract->start_date->format('d/m/Y') }} – {{ $contract->end_date->format('d/m/Y') }}
                        @include('contracts.partials.remaining-badge')
                    </td>
                    <td class="text-right">{{ $contract->amount !== null ? number_format((float) $contract->amount, 0, ',', ' ') : '—' }}</td>
                    <td><span class="badge badge-{{ $contract->status_badge }}">{{ $contract->status_label }}</span></td>
                    <td>
                        @if($contract->file_path)
                            <a href="{{ route('contracts.download', $contract) }}" title="{{ $contract->file_original_name }}"><i class="fas fa-file-download"></i></a>
                        @else
                            <span class="text-muted">—</span>
                        @endif
                    </td>
                    <td class="text-right">
                        <a href="{{ route('contracts.show', $contract) }}" class="btn btn-sm btn-info"><i class="fas fa-eye"></i></a>
                        @can('update', $contract)
                        <a href="{{ route('contracts.edit', $contract) }}" class="btn btn-sm btn-primary"><i class="fas fa-edit"></i></a>
                        @endcan
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="text-center text-muted">Aucun contrat trouvé</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="card-footer clearfix">
        <div class="float-right">
            {{ $contracts->links('pagination.bootstrap-4') }}
        </div>
    </div>
</div>
@endsection
