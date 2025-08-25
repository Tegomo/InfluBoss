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
                <i class="fas fa-plus mr-1"></i> Ajouter un Embassadeur
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
                    <input type="text" name="search" class="form-control form-control-sm" placeholder="Rechercher..." value="{{ request('search') }}">
                </div>
                <div class="col-md-3 mb-2">
                    <select name="is_active" class="form-control form-control-sm">
                        <option value="">État (Tous)</option>
                        <option value="1" {{ request('is_active') === '1' ? 'selected' : '' }}>Actifs</option>
                        <option value="0" {{ request('is_active') === '0' ? 'selected' : '' }}>Inactifs</option>
                    </select>
                </div>
                <div class="col-md-3 mb-2">
                    <select name="platform" class="form-control form-control-sm">
                        <option value="">Plateforme (Toutes)</option>
                        <option value="instagram" {{ request('platform') === 'instagram' ? 'selected' : '' }}>Instagram</option>
                        <option value="facebook" {{ request('platform') === 'facebook' ? 'selected' : '' }}>Facebook</option>
                        <option value="twitter" {{ request('platform') === 'twitter' ? 'selected' : '' }}>Twitter</option>
                        <option value="tiktok" {{ request('platform') === 'tiktok' ? 'selected' : '' }}>TikTok</option>
                        <option value="youtube" {{ request('platform') === 'youtube' ? 'selected' : '' }}>YouTube</option>
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
                    <th>Réseaux sociaux</th>
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
                                <div class="bg-light rounded-circle p-2" style="width: 40px; height: 40px; display: flex; align-items: center; justify-content: center;">
                                    <i class="fas fa-user text-secondary"></i>
                                </div>
                            </div>
                            <span>{{ $influencer->name }}</span>
                        </div>
                    </td>
                    <td>{{ $influencer->pseudo ?? 'Non renseigné' }}</td>
                    <td>{{ $influencer->email }}</td>
                    <td>{{ $influencer->city ?? 'Non renseigné' }}</td>
                    <td>
                        @php
                            $socialLinks = [];
                            if (!empty($influencer->social_links)) {
                                if (is_string($influencer->social_links)) {
                                    $socialLinks = json_decode($influencer->social_links, true) ?? [];
                                } elseif (is_array($influencer->social_links) || is_object($influencer->social_links)) {
                                    $socialLinks = (array) $influencer->social_links;
                                }
                            }
                        @endphp
                        
                        @if(!empty($socialLinks))
                            @foreach($socialLinks as $platform => $link)
                                @if($platform == 'facebook')
                                    <i class="fab fa-facebook text-primary" title="{{ $link }}"></i>
                                @elseif($platform == 'instagram')
                                    <i class="fab fa-instagram text-danger" title="{{ $link }}"></i>
                                @elseif($platform == 'twitter')
                                    <i class="fab fa-twitter text-info" title="{{ $link }}"></i>
                                @elseif($platform == 'tiktok')
                                    <i class="fab fa-tiktok" title="{{ $link }}"></i>
                                @elseif($platform == 'youtube')
                                    <i class="fab fa-youtube text-danger" title="{{ $link }}"></i>
                                @endif
                                &nbsp;
                            @endforeach
                        @else
                            -
                        @endif
                    </td>
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
                    <td colspan="5" class="text-center">Aucun influenceur trouvé</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <!-- /.card-body -->
    <div class="card-footer clearfix">
        <div class="float-right">
            {{ $influencers->appends(request()->query())->onEachSide(2)->links('pagination.bootstrap-4') }}
        </div>
    </div>
</div>
<!-- /.card -->
@endsection
