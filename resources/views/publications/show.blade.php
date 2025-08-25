@extends('layouts.adminlte')

@section('title', 'Détails de la publication')

@section('content_header')
<div class="container-fluid">
    <div class="row mb-2">
        <div class="col-sm-6">
            <h1>Publication #{{ $publication->id }}</h1>
        </div>
        <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="{{ route('publications.index') }}">Publications</a></li>
                <li class="breadcrumb-item active">Détails</li>
            </ol>
        </div>
    </div>
</div>
@endsection

@section('content')
<div class="container-fluid">
    <div class="row">
        <!-- Informations de la publication -->
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Détails de la publication</h3>
                    <div class="card-tools">
                        <a href="{{ $publication->url }}" target="_blank" class="btn btn-sm btn-primary">
                            <i class="fas fa-external-link-alt"></i> Voir sur {{ ucfirst($publication->platform) }}
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-7">
                            <div class="mb-4">
                                <div class="d-flex align-items-center mb-3">
                                    @switch($publication->platform)
                                        @case('instagram')
                                            <i class="fab fa-instagram fa-2x text-danger mr-2"></i>
                                            @break
                                        @case('facebook')
                                            <i class="fab fa-facebook fa-2x text-primary mr-2"></i>
                                            @break
                                        @case('twitter')
                                            <i class="fab fa-twitter fa-2x text-info mr-2"></i>
                                            @break
                                        @case('tiktok')
                                            <i class="fab fa-tiktok fa-2x text-dark mr-2"></i>
                                            @break
                                        @case('youtube')
                                            <i class="fab fa-youtube fa-2x text-danger mr-2"></i>
                                            @break
                                        @case('linkedin')
                                            <i class="fab fa-linkedin fa-2x text-primary mr-2"></i>
                                            @break
                                        @default
                                            <i class="fas fa-globe fa-2x text-secondary mr-2"></i>
                                    @endswitch
                                    <h3>Publication sur {{ ucfirst($publication->platform) }}</h3>
                                </div>

                                @if($publication->platform === 'instagram' && $publication->embed_code)
                                    <div class="embed-responsive embed-responsive-1by1 mb-4">
                                        {!! $publication->embed_code !!}
                                    </div>
                                @elseif($publication->platform === 'youtube' && $publication->embed_code)
                                    <div class="embed-responsive embed-responsive-16by9 mb-4">
                                        {!! $publication->embed_code !!}
                                    </div>
                                @elseif($publication->platform === 'facebook' && $publication->embed_code)
                                    <div class="fb-post mb-4">
                                        {!! $publication->embed_code !!}
                                    </div>
                                @elseif($publication->screenshot_path)
                                    <div class="text-center mb-4">
                                        <img src="{{ asset('storage/' . $publication->screenshot_path) }}" alt="Capture d'écran de la publication" 
                                            class="img-fluid rounded border">
                                    </div>
                                @else
                                    <div class="alert alert-info mb-4">
                                        <i class="icon fas fa-info-circle"></i> Aucun aperçu disponible pour cette publication.
                                        <a href="{{ $publication->url }}" target="_blank" class="alert-link">Voir directement sur {{ ucfirst($publication->platform) }}</a>.
                                    </div>
                                @endif
                                
                                @if($publication->content)
                                    <div class="card mb-4">
                                        <div class="card-header">
                                            <h5 class="mb-0">Contenu de la publication</h5>
                                        </div>
                                        <div class="card-body bg-light">
                                            <p class="mb-0">{{ $publication->content }}</p>
                                        </div>
                                    </div>
                                @endif
                            </div>
                            
                            <div class="card">
                                <div class="card-header">
                                    <h5 class="mb-0">Informations générales</h5>
                                </div>
                                <div class="card-body p-0">
                                    <table class="table">
                                        <tr>
                                            <td width="30%">Embassadeur:</td>
                                            <td>
                                                <a href="{{ route('influencers.show', $publication->influencer) }}">
                                                    {{ $publication->influencer->name ?? 'Non défini' }}
                                                </a>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>Campagne:</td>
                                            <td>
                                                <a href="{{ route('campaigns.show', $publication->assignment->campaign) }}">
                                                    {{ $publication->assignment->campaign->title ?? 'Non définie' }}
                                                </a>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>Mission:</td>
                                            <td>
                                                <a href="{{ route('assignments.show', $publication->assignment) }}">
                                                    Mission #{{ $publication->assignment->id }}
                                                </a>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>Statut:</td>
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
                                        </tr>
                                        <tr>
                                            <td>Date de publication:</td>
                                            <td>{{ $publication->published_at ? $publication->published_at->format('d/m/Y H:i') : 'Non définie' }}</td>
                                        </tr>
                                        <tr>
                                            <td>Date d'ajout:</td>
                                            <td>{{ $publication->created_at->format('d/m/Y H:i') }}</td>
                                        </tr>
                                        <tr>
                                            <td>URL:</td>
                                            <td>
                                                <a href="{{ $publication->post_url }}" target="_blank" class="text-break">
                                                    {{ $publication->post_url }}
                                                </a>
                                            </td>
                                        </tr>
                                    </table>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-5">
                            <div class="card mb-4">
                                <div class="card-header bg-orange">
                                    <h5 class="mb-0 text-white">Performances</h5>
                                </div>
                                <div class="card-body">
                                    @if($publication->latestStats)
                                        <div class="row">
                                            <div class="col-md-4 text-center mb-4">
                                                <div class="h1 mb-0">
                                                    <i class="far fa-thumbs-up text-primary"></i>
                                                </div>
                                                <div class="h3 mb-0">{{ $publication->latestStats->likes ?? 0 }}</div>
                                                <div class="text-muted">J'aime</div>
                                            </div>
                                            <div class="col-md-4 text-center mb-4">
                                                <div class="h1 mb-0">
                                                    <i class="far fa-comment text-info"></i>
                                                </div>
                                                <div class="h3 mb-0">{{ $publication->latestStats->comments ?? 0 }}</div>
                                                <div class="text-muted">Commentaires</div>
                                            </div>
                                            <div class="col-md-4 text-center mb-4">
                                                <div class="h1 mb-0">
                                                    <i class="fas fa-share text-success"></i>
                                                </div>
                                                <div class="h3 mb-0">{{ $publication->latestStats->shares ?? 0 }}</div>
                                                <div class="text-muted">Partages</div>
                                            </div>
                                        </div>
                                        
                                        @if($publication->latestStats->views)
                                        <div class="row">
                                            <div class="col-md-6 text-center mb-4">
                                                <div class="h1 mb-0">
                                                    <i class="far fa-eye text-secondary"></i>
                                                </div>
                                                <div class="h3 mb-0">{{ $publication->latestStats->views }}</div>
                                                <div class="text-muted">Vues</div>
                                            </div>
                                            <div class="col-md-6 text-center mb-4">
                                                <div class="h1 mb-0">
                                                    <i class="fas fa-percentage text-warning"></i>
                                                </div>
                                                <div class="h3 mb-0">{{ $publication->latestStats->engagement_rate ?? '0%' }}</div>
                                                <div class="text-muted">Taux d'engagement</div>
                                            </div>
                                        </div>
                                        @endif
                                        
                                        <hr>
                                        <div class="text-muted mb-0">
                                            <i class="fas fa-clock"></i> Dernière mise à jour: 
                                            {{ $publication->latestStats->updated_at->format('d/m/Y H:i') }}
                                        </div>
                                        <div class="text-right">
                                            <form action="{{ route('publications.refresh-stats', $publication) }}" method="POST">
                                                @csrf
                                                <button type="submit" class="btn btn-sm btn-primary">
                                                    <i class="fas fa-sync-alt"></i> Rafraîchir
                                                </button>
                                            </form>
                                        </div>
                                    @else
                                        <div class="text-center py-4">
                                            <div class="mb-3">
                                                <i class="fas fa-chart-bar fa-3x text-muted"></i>
                                            </div>
                                            <p class="mb-3">Aucune statistique disponible pour cette publication.</p>
                                            <form action="{{ route('publications.refresh-stats', $publication) }}" method="POST">
                                                @csrf
                                                <button type="submit" class="btn btn-primary">
                                                    <i class="fas fa-sync-alt"></i> Récupérer les statistiques
                                                </button>
                                            </form>
                                        </div>
                                    @endif
                                </div>
                            </div>
                            
                            <div class="card">
                                <div class="card-header">
                                    <h5 class="mb-0">Actions</h5>
                                </div>
                                <div class="card-body">
                                    <div class="d-flex flex-column">
                                        @if($publication->status == 'pending')
                                            <form action="{{ route('publications.verify', $publication) }}" method="POST" class="mb-2">
                                                @csrf
                                                <button type="submit" class="btn btn-success btn-block">
                                                    <i class="fas fa-check"></i> Vérifier la publication
                                                </button>
                                            </form>
                                            
                                            <form action="{{ route('publications.reject', $publication) }}" method="POST" class="mb-2">
                                                @csrf
                                                <button type="submit" class="btn btn-danger btn-block">
                                                    <i class="fas fa-times"></i> Rejeter la publication
                                                </button>
                                            </form>
                                        @endif
                                        
                                        @can('update', $publication)
                                        <a href="{{ route('publications.edit', $publication) }}" class="btn btn-warning btn-block mb-2">
                                            <i class="fas fa-edit"></i> Modifier
                                        </a>
                                        @endcan
                                        
                                        @if(!$publication->screenshot_path)
                                            <form action="{{ route('publications.capture-screenshot', $publication) }}" method="POST" class="mb-2">
                                                @csrf
                                                <button type="submit" class="btn btn-info btn-block">
                                                    <i class="fas fa-camera"></i> Capturer un aperçu
                                                </button>
                                            </form>
                                        @endif
                                        
                                        <form action="{{ route('publications.destroy', $publication) }}" method="POST"
                                              onsubmit="return confirm('Êtes-vous sûr de vouloir supprimer cette publication ?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-outline-danger btn-block">
                                                <i class="fas fa-trash"></i> Supprimer
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Contenus utilisés -->
        <div class="col-md-4">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Contenus utilisés</h3>
                </div>
                <div class="card-body p-0">
                    @if($publication->contents && $publication->contents->count() > 0)
                        <div class="list-group">
                            @foreach($publication->contents as $content)
                                <div class="list-group-item">
                                    <div class="d-flex">
                                        <div class="mr-3">
                                            @if($content->content_type == 'image')
                                                <img src="{{ asset('storage/' . $content->file_path) }}" 
                                                    alt="{{ $content->title }}" style="width: 80px; height: 80px; object-fit: cover;" 
                                                    class="img-thumbnail">
                                            @elseif($content->content_type == 'video')
                                                <div class="d-flex align-items-center justify-content-center bg-light" 
                                                    style="width: 80px; height: 80px;">
                                                    <i class="fas fa-film fa-2x text-primary"></i>
                                                </div>
                                            @else
                                                <div class="d-flex align-items-center justify-content-center bg-light" 
                                                    style="width: 80px; height: 80px;">
                                                    <i class="fas fa-file-alt fa-2x text-secondary"></i>
                                                </div>
                                            @endif
                                        </div>
                                        <div>
                                            <h5 class="mb-1">{{ $content->title }}</h5>
                                            <p class="mb-1 text-muted">{{ ucfirst($content->content_type) }}</p>
                                            <div>
                                                <a href="{{ route('contents.show', $content) }}" class="btn btn-sm btn-info">
                                                    <i class="fas fa-eye"></i> Voir
                                                </a>
                                                <a href="{{ route('contents.download', $content) }}" class="btn btn-sm btn-success">
                                                    <i class="fas fa-download"></i> Télécharger
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="text-center py-4">
                            <p>Aucun contenu associé à cette publication</p>
                        </div>
                    @endif
                </div>
            </div>
            
            <!-- Historique des statistiques -->
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Historique des statistiques</h3>
                </div>
                <div class="card-body p-0">
                    @if($publication->stats && $publication->stats->count() > 0)
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <th>J'aime</th>
                                    <th>Com.</th>
                                    <th>Partages</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($publication->stats->sortByDesc('created_at')->take(5) as $stat)
                                    <tr>
                                        <td>{{ $stat->created_at->format('d/m/Y') }}</td>
                                        <td>{{ $stat->likes }}</td>
                                        <td>{{ $stat->comments }}</td>
                                        <td>{{ $stat->shares }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                        @if($publication->stats->count() > 5)
                            <div class="text-center py-2">
                                <a href="{{ route('publications.stats', $publication) }}" class="btn btn-sm btn-default">
                                    Voir tout l'historique
                                </a>
                            </div>
                        @endif
                    @else
                        <div class="text-center py-4">
                            <p>Aucun historique de statistiques disponible</p>
                        </div>
                    @endif
                </div>
            </div>
            
            <!-- Commentaires et notes -->
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Notes internes</h3>
                </div>
                <div class="card-body">
                    <form action="{{ route('publications.add-note', $publication) }}" method="POST">
                        @csrf
                        <div class="form-group">
                            <textarea class="form-control" name="note" rows="3" placeholder="Ajouter une note..."></textarea>
                        </div>
                        <div class="text-right">
                            <button type="submit" class="btn btn-primary">Ajouter</button>
                        </div>
                    </form>
                    
                    <hr>
                    
                    @if($publication->notes && $publication->notes->count() > 0)
                        <div class="timeline timeline-inverse">
                            @foreach($publication->notes->sortByDesc('created_at') as $note)
                                <div>
                                    <i class="fas fa-comments bg-orange"></i>
                                    <div class="timeline-item">
                                        <span class="time">
                                            <i class="far fa-clock"></i> {{ $note->created_at->format('d/m/Y H:i') }}
                                        </span>
                                        <h3 class="timeline-header">
                                            <a href="#">{{ $note->user->name ?? 'Utilisateur' }}</a>
                                        </h3>
                                        <div class="timeline-body">
                                            {{ $note->content }}
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <p class="text-center text-muted">Aucune note pour le moment</p>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
