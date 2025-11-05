@extends('layouts.adminlte')

@section('title', 'Gestion des publications')

@section('content_header')
<div class="container-fluid">
    <div class="row mb-2">
        <div class="col-sm-6">
            <h1>Publications</h1>
        </div>
        <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                <li class="breadcrumb-item active">Publications</li>
            </ol>
        </div>
    </div>
</div>
@endsection

@section('content')
<div class="container-fluid">
    <!-- Carte de statistiques globales -->
    <div class="row">
        <div class="col-12">
            <div class="card bg-gradient-info">
                <div class="card-header">
                    <h3 class="card-title">Vue d'ensemble des statistiques</h3>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-3 col-sm-6 col-12">
                            <div class="info-box bg-gradient-success">
                                <span class="info-box-icon"><i class="far fa-thumbs-up"></i></span>
                                <div class="info-box-content">
                                    <span class="info-box-text">Likes</span>
                                    <span class="info-box-number">{{ number_format($globalStats['likes']) }}</span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3 col-sm-6 col-12">
                            <div class="info-box bg-gradient-primary">
                                <span class="info-box-icon"><i class="far fa-comment"></i></span>
                                <div class="info-box-content">
                                    <span class="info-box-text">Commentaires</span>
                                    <span class="info-box-number">{{ number_format($globalStats['comments']) }}</span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3 col-sm-6 col-12">
                            <div class="info-box bg-gradient-warning">
                                <span class="info-box-icon"><i class="fas fa-share"></i></span>
                                <div class="info-box-content">
                                    <span class="info-box-text">Partages</span>
                                    <span class="info-box-number">{{ number_format($globalStats['shares']) }}</span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3 col-sm-6 col-12">
                            <div class="info-box bg-gradient-danger">
                                <span class="info-box-icon"><i class="fas fa-chart-line"></i></span>
                                <div class="info-box-content">
                                    <span class="info-box-text">Engagement total</span>
                                    <span class="info-box-number">{{ number_format($globalStats['likes'] + $globalStats['comments'] + $globalStats['shares']) }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Liste des publications -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Liste des publications</h3>
                    <div class="card-tools">
                        <a href="{{ route('publications.export', request()->query()) }}" class="btn btn-success btn-sm mr-2">
                            <i class="fas fa-file-excel mr-1"></i> Exporter en Excel
                        </a>
                        <a href="{{ route('publications.create') }}" class="btn btn-primary btn-sm">
                            <i class="fas fa-plus mr-1"></i> Nouvelle publication
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <!-- Filtres -->
                    <form action="{{ route('publications.index') }}" method="GET" class="mb-4">
                        <div class="row">
                            <div class="col-md-2">
                                <div class="form-group">
                                    <label for="campaign_id">Campagne</label>
                                    <select class="form-control" id="campaign_id" name="campaign_id">
                                        <option value="">Toutes</option>
                                        @foreach($campaigns as $campaign)
                                            <option value="{{ $campaign->id }}" {{ request('campaign_id') == $campaign->id ? 'selected' : '' }}>
                                                {{ $campaign->title }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="form-group">
                                    <label for="platform">Plateforme</label>
                                    <select class="form-control" id="platform" name="platform">
                                        <option value="">Toutes</option>
                                        <option value="instagram" {{ request('platform') == 'instagram' ? 'selected' : '' }}>Instagram</option>
                                        <option value="facebook" {{ request('platform') == 'facebook' ? 'selected' : '' }}>Facebook</option>
                                        <option value="twitter" {{ request('platform') == 'twitter' ? 'selected' : '' }}>Twitter</option>
                                        <option value="tiktok" {{ request('platform') == 'tiktok' ? 'selected' : '' }}>TikTok</option>
                                        <option value="youtube" {{ request('platform') == 'youtube' ? 'selected' : '' }}>YouTube</option>
                                        <option value="linkedin" {{ request('platform') == 'linkedin' ? 'selected' : '' }}>LinkedIn</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="form-group">
                                    <label for="status">Statut</label>
                                    <select class="form-control" id="status" name="status">
                                        <option value="">Tous</option>
                                        <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>En attente</option>
                                        <option value="verified" {{ request('status') == 'verified' ? 'selected' : '' }}>Vérifiée</option>
                                        <option value="rejected" {{ request('status') == 'rejected' ? 'selected' : '' }}>Rejetée</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="search">Recherche</label>
                                    <input type="text" class="form-control" id="search" name="search" 
                                        placeholder="Rechercher par influenceur ou contenu..." value="{{ request('search') }}">
                                </div>
                            </div>
                            <div class="col-md-2 d-flex align-items-end">
                                <button type="submit" class="btn btn-primary mr-2">Filtrer</button>
                                <a href="{{ route('publications.index') }}" class="btn btn-secondary">Réinitialiser</a>
                            </div>
                        </div>
                    </form>

                    <!-- Tableau des publications -->
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Plateforme</th>
                                    <th>Embassadeur</th>
                                    <th>Campagne</th>
                                    <th>URL</th>
                                    <th>Statut</th>
                                    <th>Statistiques</th>
                                    <th>Date de publication</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($publications as $publication)
                                <tr>
                                    <td>{{ $publication->id }}</td>
                                    <td class="text-center">
                                        @switch($publication->platform)
                                            @case('instagram')
                                                <i class="fab fa-instagram fa-2x text-danger"></i>
                                                @break
                                            @case('facebook')
                                                <i class="fab fa-facebook fa-2x text-primary"></i>
                                                @break
                                            @case('twitter')
                                                <i class="fab fa-twitter fa-2x text-info"></i>
                                                @break
                                            @case('tiktok')
                                                <i class="fab fa-tiktok fa-2x text-dark"></i>
                                                @break
                                            @case('youtube')
                                                <i class="fab fa-youtube fa-2x text-danger"></i>
                                                @break
                                            @case('linkedin')
                                                <i class="fab fa-linkedin fa-2x text-primary"></i>
                                                @break
                                            @default
                                                <i class="fas fa-globe fa-2x text-secondary"></i>
                                        @endswitch
                                        <div><small>{{ ucfirst($publication->platform) }}</small></div>
                                    </td>
                                    <td>{{ $publication->influencer->name ?? 'N/A' }}</td>
                                    <td>{{ $publication->assignment->campaign->name ?? 'N/A' }}</td>
                                    <td>
                                        <a href="{{ $publication->post_url }}" target="_blank" class="btn btn-sm btn-outline-secondary">
                                            <i class="fas fa-external-link-alt"></i> Voir
                                        </a>
                                    </td>
                                    <td>
                                        @switch($publication->status)
                                            @case('pending')
                                                <span class="badge badge-warning">En attente</span>
                                                @break
                                            @case('verified')
                                                <span class="badge badge-success">Vérifiée</span>
                                                @break
                                            @case('rejected')
                                                <span class="badge badge-danger">Rejetée</span>
                                                @break
                                            @default
                                                <span class="badge badge-secondary">{{ $publication->status }}</span>
                                        @endswitch
                                    </td>
                                    <td>
                                        <div class="small">
                                            @if($publication->latestStats)
                                                <i class="far fa-thumbs-up"></i> {{ $publication->latestStats->likes ?? 0 }}
                                                <i class="far fa-comment ml-1"></i> {{ $publication->latestStats->comments ?? 0 }}
                                                <i class="fas fa-share ml-1"></i> {{ $publication->latestStats->shares ?? 0 }}
                                            @else
                                                <span class="text-muted">Aucune statistique</span>
                                            @endif
                                        </div>
                                    </td>
                                    <td>{{ $publication->published_at ? $publication->published_at->format('d/m/Y H:i') : 'N/A' }}</td>
                                    <td>
                                        <div class="btn-group">
                                            <a href="{{ route('publications.show', $publication) }}" class="btn btn-sm btn-info">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            @if($publication->status == 'pending')
                                                <form action="{{ route('publications.verify', $publication) }}" method="POST" style="display: inline-block;">
                                                    @csrf
                                                    <button type="submit" class="btn btn-sm btn-success" title="Vérifier">
                                                        <i class="fas fa-check"></i>
                                                    </button>
                                                </form>
                                            @endif
                                            <form action="{{ route('publications.refresh-stats', $publication) }}" method="POST" style="display: inline-block;">
                                                @csrf
                                                <button type="submit" class="btn btn-sm btn-primary" title="Rafraîchir les stats">
                                                    <i class="fas fa-sync-alt"></i>
                                                </button>
                                            </form>
                                            <form action="{{ route('publications.destroy', $publication) }}" method="POST" 
                                                onsubmit="return confirm('Êtes-vous sûr de vouloir supprimer cette publication ?');"
                                                style="display: inline-block;">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-danger">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="9" class="text-center">Aucune publication trouvée</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="card-footer clearfix">
                    <div class="float-right">
                        {{ $publications->appends(request()->query())->onEachSide(2)->links('pagination.bootstrap-4') }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
