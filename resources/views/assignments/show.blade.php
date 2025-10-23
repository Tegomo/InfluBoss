@extends('layouts.adminlte')

@section('title', 'Détails de la mission')

@section('content_header')
<div class="container-fluid">
    <div class="row mb-2">
        <div class="col-sm-6">
            <h1>Détails de la mission #{{ $assignment->id }}</h1>
        </div>
        <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="{{ route('assignments.index') }}">Missions</a></li>
                <li class="breadcrumb-item active">Détails</li>
            </ol>
        </div>
    </div>
</div>
@endsection

@section('content')
<div class="container-fluid">
    <!-- Vue d'ensemble de la mission -->
    <div class="row">
        <div class="col-md-4">
            <div class="card card-widget widget-user">
                <div class="widget-user-header bg-orange">
                    <h3 class="widget-user-username">{{ $assignment->influencer->name ?? 'Influenceur non défini' }}</h3>
                    <h5 class="widget-user-desc">{{ $assignment->influencer->profile->specialty ?? 'Spécialité non définie' }}</h5>
                </div>
                <div class="widget-user-image">
                    <img class="img-circle elevation-2" src="{{ $assignment->influencer->profile->avatar_url ?? asset('img/default-avatar.png') }}" alt="Photo de l'influenceur">
                </div>
                <div class="card-footer">
                    <div class="row">
                        <div class="col-sm-4 border-right">
                            <div class="description-block">
                                <h5 class="description-header">{{ $assignment->influencer->profile->followers_count ?? 0 }}</h5>
                                <span class="description-text">ABONNÉS</span>
                            </div>
                        </div>
                        <div class="col-sm-4 border-right">
                            <div class="description-block">
                                <h5 class="description-header">{{ $assignment->publications->count() ?? 0 }}</h5>
                                <span class="description-text">PUBLICATIONS</span>
                            </div>
                        </div>
                        <div class="col-sm-4">
                            <div class="description-block">
                                <h5 class="description-header">{{ $assignment->influencer->profile->engagement_rate ?? '0%' }}</h5>
                                <span class="description-text">ENGAGEMENT</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Contacts</h3>
                </div>
                <div class="card-body p-0">
                    <table class="table">
                        <tr>
                            <td><i class="fas fa-envelope"></i></td>
                            <td>Email:</td>
                            <td>{{ $assignment->influencer->email ?? 'N/A' }}</td>
                        </tr>
                        <tr>
                            <td><i class="fas fa-phone"></i></td>
                            <td>Téléphone:</td>
                            <td>{{ $assignment->influencer->profile->phone ?? 'N/A' }}</td>
                        </tr>
                        <tr>
                            <td><i class="fab fa-instagram"></i></td>
                            <td>Instagram:</td>
                            <td>
                                @if($assignment->influencer->profile->instagram_handle ?? null)
                                    <a href="https://instagram.com/{{ $assignment->influencer->profile->instagram_handle }}" target="_blank">
                                        {{ '@' . $assignment->influencer->profile->instagram_handle }}
                                    </a>
                                @else
                                    N/A
                                @endif
                            </td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>

        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Informations sur la mission</h3>
                    <div class="card-tools">
                        <a href="{{ route('assignments.edit', $assignment) }}" class="btn btn-sm btn-warning">
                            <i class="fas fa-edit"></i> Modifier
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="info-box">
                                <span class="info-box-icon bg-primary"><i class="fas fa-bullhorn"></i></span>
                                <div class="info-box-content">
                                    <span class="info-box-text">Campagne</span>
                                    <span class="info-box-number">
                                        <a href="{{ route('campaigns.show', $assignment->campaign) }}">
                                            {{ $assignment->campaign->name ?? 'N/A' }}
                                        </a>
                                    </span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="info-box">
                                <span class="info-box-icon bg-warning"><i class="fas fa-user-tie"></i></span>
                                <div class="info-box-content">
                                    <span class="info-box-text">Superviseur</span>
                                    <span class="info-box-number">{{ $assignment->supervisor->name ?? 'N/A' }}</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row mt-3">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Statut:</label>
                                <div>
                                    @switch($assignment->status)
                                        @case('pending')
                                            <span class="badge badge-warning">En attente</span>
                                            @break
                                        @case('accepted')
                                            <span class="badge badge-info">Acceptée</span>
                                            @break
                                        @case('in_progress')
                                            <span class="badge badge-primary">En cours</span>
                                            @break
                                        @case('completed')
                                            <span class="badge badge-success">Terminée</span>
                                            @break
                                        @default
                                            <span class="badge badge-secondary">{{ $assignment->status }}</span>
                                    @endswitch
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Date d'assignation:</label>
                                <div>{{ $assignment->created_at->format('d/m/Y') }}</div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Date d'échéance:</label>
                                <div>{{ $assignment->deadline ? $assignment->deadline->format('d/m/Y') : 'Non définie' }}</div>
                            </div>
                        </div>
                    </div>

                    <div class="row mt-3">
                        <div class="col-md-12">
                            <div class="form-group">
                                <label>Description de la mission:</label>
                                <div class="p-3 bg-light rounded">
                                    {{ $assignment->description ?: 'Aucune description fournie.' }}
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row mt-3">
                        <div class="col-md-12">
                            <div class="form-group">
                                <label>Instructions spécifiques:</label>
                                <div class="p-3 bg-light rounded">
                                    {{ $assignment->instructions ?: 'Aucune instruction spécifique fournie.' }}
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row mt-3">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Compensation:</label>
                                <div>{{ $assignment->compensation ? number_format($assignment->compensation, 2, ',', ' ') . ' FCFA' : 'Non définie' }}</div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Publications requises:</label>
                                <div>{{ $assignment->required_posts ?? 'Non spécifié' }}</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Contenus associés -->
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Contenus de la campagne</h3>
                </div>
                <div class="card-body">
                    <div class="row">
                        @forelse ($assignment->campaign->contents ?? [] as $content)
                            <div class="col-md-3 mb-4">
                                <div class="card h-100">
                                    <div class="card-header p-2 text-center">
                                        @switch($content->type)
                                            @case('image')
                                                <span class="badge badge-info">Image</span>
                                                @break
                                            @case('video')
                                                <span class="badge badge-danger">Vidéo</span>
                                                @break
                                            @case('text')
                                                <span class="badge badge-success">Texte</span>
                                                @break
                                            @case('link')
                                                <span class="badge badge-primary">Lien</span>
                                                @break
                                            @default
                                                <span class="badge badge-secondary">{{ ucfirst($content->type) }}</span>
                                        @endswitch
                                    </div>
                                    <div class="card-body text-center p-2">
                                        @if($content->type == 'image')
                                            <img src="{{ asset('storage/' . $content->file_path) }}" alt="{{ $content->title }}" 
                                                class="img-fluid" style="max-height: 150px;">
                                        @elseif($content->type == 'video')
                                            <div class="text-center py-5">
                                                <i class="fas fa-video fa-4x text-danger"></i>
                                            </div>
                                        @elseif($content->type == 'text')
                                            <div class="text-center py-5">
                                                <i class="fas fa-file-alt fa-4x text-success"></i>
                                            </div>
                                        @elseif($content->type == 'link')
                                            <div class="text-center py-5">
                                                <i class="fas fa-link fa-4x text-primary"></i>
                                            </div>
                                        @else
                                            <div class="text-center py-5">
                                                <i class="fas fa-file fa-4x text-secondary"></i>
                                            </div>
                                        @endif
                                    </div>
                                    <div class="card-footer p-2">
                                        <h5 class="text-center">{{ $content->title }}</h5>
                                        <div class="text-center mt-2">
                                            <a href="{{ route('contents.show', $content) }}" class="btn btn-sm btn-info">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <a href="{{ route('contents.download', $content) }}" class="btn btn-sm btn-success">
                                                <i class="fas fa-download"></i>
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @empty
                            <div class="col-12">
                                <div class="alert alert-info">
                                    Aucun contenu disponible pour cette campagne.
                                </div>
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Publications de l'influenceur -->
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Publications réalisées</h3>
                    <div class="card-tools">
                        <a href="{{ route('publications.create', ['assignment_id' => $assignment->id]) }}" class="btn btn-sm btn-primary">
                            <i class="fas fa-plus"></i> Ajouter une publication
                        </a>
                    </div>
                </div>
                <div class="card-body table-responsive">
                    <table class="table table-bordered table-striped">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Plateforme</th>
                                <th>URL</th>
                                <th>Date de publication</th>
                                <th>Statut</th>
                                <th>Statistiques</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($assignment->publications ?? [] as $publication)
                            <tr>
                                <td>{{ $publication->id }}</td>
                                <td class="text-center">
                                    @switch($publication->platform)
                                        @case('instagram')
                                            <i class="fab fa-instagram fa-2x text-danger"></i>
                                            <div><small>Instagram</small></div>
                                            @break
                                        @case('facebook')
                                            <i class="fab fa-facebook fa-2x text-primary"></i>
                                            <div><small>Facebook</small></div>
                                            @break
                                        @case('twitter')
                                            <i class="fab fa-twitter fa-2x text-info"></i>
                                            <div><small>Twitter</small></div>
                                            @break
                                        @case('tiktok')
                                            <i class="fab fa-tiktok fa-2x text-dark"></i>
                                            <div><small>TikTok</small></div>
                                            @break
                                        @case('youtube')
                                            <i class="fab fa-youtube fa-2x text-danger"></i>
                                            <div><small>YouTube</small></div>
                                            @break
                                        @default
                                            <i class="fas fa-globe fa-2x text-secondary"></i>
                                            <div><small>{{ ucfirst($publication->platform) }}</small></div>
                                    @endswitch
                                </td>
                                <td>
                                    <a href="{{ $publication->url }}" target="_blank" class="btn btn-sm btn-outline-secondary">
                                        <i class="fas fa-external-link-alt"></i> Voir
                                    </a>
                                </td>
                                <td>{{ $publication->published_at ? $publication->published_at->format('d/m/Y H:i') : 'N/A' }}</td>
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
                                    </div>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="7" class="text-center">Aucune publication pour cette mission</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Historique de la mission -->
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Historique</h3>
                </div>
                <div class="card-body p-0">
                    <div class="timeline timeline-inverse p-3">
                        <!-- Date de création -->
                        <div class="time-label">
                            <span class="bg-orange">
                                {{ $assignment->created_at->format('d M Y') }}
                            </span>
                        </div>
                        <div>
                            <i class="fas fa-plus bg-primary"></i>
                            <div class="timeline-item">
                                <span class="time"><i class="far fa-clock"></i> {{ $assignment->created_at->format('H:i') }}</span>
                                <h3 class="timeline-header">Mission créée</h3>
                                <div class="timeline-body">
                                    Mission assignée à {{ $assignment->influencer->name ?? 'un influenceur' }} par {{ $assignment->creator->name ?? 'un administrateur' }}.
                                </div>
                            </div>
                        </div>

                        @if($assignment->status != 'pending')
                        <!-- Acceptation de la mission -->
                        <div class="time-label">
                            <span class="bg-info">
                                {{ $assignment->accepted_at ? $assignment->accepted_at->format('d M Y') : $assignment->created_at->addDays(1)->format('d M Y') }}
                            </span>
                        </div>
                        <div>
                            <i class="fas fa-check bg-info"></i>
                            <div class="timeline-item">
                                <span class="time"><i class="far fa-clock"></i> {{ $assignment->accepted_at ? $assignment->accepted_at->format('H:i') : '10:00' }}</span>
                                <h3 class="timeline-header">Mission acceptée</h3>
                                <div class="timeline-body">
                                    {{ $assignment->influencer->name ?? "L'influenceur" }} a accepté la mission.
                                </div>
                            </div>
                        </div>
                        @endif

                        @if($assignment->status == 'completed')
                        <!-- Finalisation de la mission -->
                        <div class="time-label">
                            <span class="bg-success">
                                {{ $assignment->completed_at ? $assignment->completed_at->format('d M Y') : $assignment->updated_at->format('d M Y') }}
                            </span>
                        </div>
                        <div>
                            <i class="fas fa-trophy bg-success"></i>
                            <div class="timeline-item">
                                <span class="time"><i class="far fa-clock"></i> {{ $assignment->completed_at ? $assignment->completed_at->format('H:i') : $assignment->updated_at->format('H:i') }}</span>
                                <h3 class="timeline-header">Mission terminée</h3>
                                <div class="timeline-body">
                                    La mission a été marquée comme terminée.
                                    @if($assignment->publications_count > 0)
                                        {{ $assignment->publications_count }} publication(s) effectuée(s).
                                    @endif
                                </div>
                            </div>
                        </div>
                        @endif

                        <div>
                            <i class="far fa-clock bg-gray"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
