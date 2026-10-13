@extends('layouts.adminlte')

@section('title')
Liste des Ambassadeurs
@endsection

@section('breadcrumb')
<li class="breadcrumb-item active">Ambassadeurs</li>
@endsection

@section('content')
<div class="card">
    <div class="card-header">
        <h3 class="card-title">Liste des Ambassadeurs</h3>
        <div class="card-tools">
            @can('create', \App\Models\User::class)
            <a href="{{ route('influencers.create') }}" class="btn btn-primary btn-sm">
                <i class="fas fa-plus mr-1"></i> Ajouter un Ambassadeur
            </a>
            @endcan

            @if(auth()->user()->role === 'admin' || auth()->user()->role === 'super_admin')
            <a href="{{ route('influencers.import-export') }}" class="btn btn-success btn-sm ml-2">
                <i class="fas fa-file-csv mr-1"></i> Import/Export CSV
            </a>
            @endif
        </div>
    </div>
    <!-- /.card-header -->
    <div class="card-body table-responsive p-0">
        @if(session('success'))
        <div class="alert alert-success alert-dismissible">
            <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
            <h5><i class="icon fas fa-check"></i> Succès!</h5>
            {{ session('success') }}
        </div>
        @endif

        <!-- Filtres -->
        <div class="p-3 bg-light">
            <form action="{{ route('influencers.index') }}" method="GET" class="row">
                <div class="col-md-3 mb-2">
                    <input type="text" name="search" class="form-control form-control-sm" placeholder="Nom, pseudo ou email…" value="{{ request('search') }}">
                </div>
                <div class="col-md-2 mb-2">
                    <select name="is_active" class="form-control form-control-sm">
                        <option value="">État (Tous)</option>
                        <option value="1" {{ request('is_active') === '1' ? 'selected' : '' }}>Actifs</option>
                        <option value="0" {{ request('is_active') === '0' ? 'selected' : '' }}>Inactifs</option>
                    </select>
                </div>
                <div class="col-md-2 mb-2">
                    <select name="platform" class="form-control form-control-sm">
                        <option value="">Plateforme (Toutes)</option>
                        @foreach($platforms as $key => $cfg)
                            <option value="{{ $key }}" {{ request('platform') === $key ? 'selected' : '' }}>{{ $cfg['label'] }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2 mb-2">
                    <select name="category" class="form-control form-control-sm">
                        <option value="">Catégorie (Toutes)</option>
                        @foreach($categories as $key => $label)
                            <option value="{{ $key }}" {{ request('category') === $key ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3 mb-2">
                    <button type="submit" class="btn btn-sm btn-info mr-1">Filtrer</button>
                    <a href="{{ route('influencers.index') }}" class="btn btn-sm btn-secondary">Réinitialiser</a>
                </div>
            </form>
        </div>

        <table class="table table-hover text-nowrap">
            <thead>
                <tr>
                    <th>Nom</th>
                    <th>Pseudo</th>
                    <th>Email</th>
                    <th>Ville</th>
                    <th>Catégories</th>
                    <th>Réseaux</th>
                    <th class="text-right">Abonnés</th>
                    <th>Statut</th>
                    <th class="text-right">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($influencers as $influencer)
                <tr>
                    <td>
                        <div class="d-flex align-items-center">
                            <div class="mr-3">
                                @if($influencer->profile_photo)
                                    <img src="{{ asset('storage/' . $influencer->profile_photo) }}" class="img-circle" alt="" style="width: 40px; height: 40px; object-fit: cover;">
                                @else
                                    <div class="bg-light rounded-circle p-2" style="width: 40px; height: 40px; display: flex; align-items: center; justify-content: center;">
                                        <i class="fas fa-user text-secondary"></i>
                                    </div>
                                @endif
                            </div>
                            <a href="{{ route('influencers.show', $influencer) }}">{{ $influencer->name }}</a>
                        </div>
                    </td>
                    <td>{{ $influencer->pseudo ?? 'Non renseigné' }}</td>
                    <td>{{ $influencer->email }}</td>
                    <td>{{ $influencer->city ?? 'Non renseignée' }}</td>
                    <td>
                        @forelse($influencer->category_labels as $label)
                            <span class="badge badge-info">{{ $label }}</span>
                        @empty
                            <span class="text-muted">-</span>
                        @endforelse
                    </td>
                    <td>
                        @forelse($influencer->socialAccounts as $account)
                            <a href="{{ $account->url }}" target="_blank" rel="noopener" class="mr-1" title="{{ $account->label }} — {{ $account->followers_count !== null ? number_format($account->followers_count) . ' abonnés' : 'abonnés inconnus' }}">
                                <i class="{{ $account->config['icon'] }}" style="color: {{ $account->config['color'] }}"></i>
                            </a>
                        @empty
                            <span class="text-muted">-</span>
                        @endforelse
                    </td>
                    <td class="text-right">{{ number_format((int) ($influencer->followers_total ?? 0)) }}</td>
                    <td>
                        @if($influencer->is_active)
                            <span class="badge badge-success">Actif</span>
                        @else
                            <span class="badge badge-secondary">Inactif</span>
                        @endif
                    </td>
                    <td class="text-right">
                        <a href="{{ route('influencers.show', $influencer) }}" class="btn btn-sm btn-info">
                            <i class="fas fa-eye"></i>
                        </a>
                        @can('update', $influencer)
                        <a href="{{ route('influencers.edit', $influencer) }}" class="btn btn-sm btn-primary">
                            <i class="fas fa-edit"></i>
                        </a>
                        @endcan
                        @can('delete', $influencer)
                        <form action="{{ route('influencers.destroy', $influencer) }}" method="POST" class="d-inline" onsubmit="return confirm('Êtes-vous sûr de vouloir supprimer cet influenceur ?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-sm btn-danger">
                                <i class="fas fa-trash"></i>
                            </button>
                        </form>
                        @endcan
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="9" class="text-center">Aucun influenceur trouvé</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <!-- /.card-body -->
    <div class="card-footer clearfix">
        <div class="float-right">
            {{ $influencers->onEachSide(2)->links('pagination.bootstrap-4') }}
        </div>
    </div>
</div>
<!-- /.card -->
@endsection
