@extends('layouts.adminlte')

@php
use Illuminate\Support\Facades\Schema;
@endphp

@section('title', 'Détails du contenu')

@section('content_header')
<div class="container-fluid">
    <div class="row mb-2">
        <div class="col-sm-6">
            <h1>{{ $content->title }}</h1>
        </div>
        <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="{{ route('contents.index') }}">Contenus</a></li>
                <li class="breadcrumb-item active">{{ $content->title }}</li>
            </ol>
        </div>
    </div>
</div>
@endsection

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Aperçu du contenu</h3>
                    <div class="card-tools">
                        <a href="{{ route('contents.download', $content) }}" class="btn btn-sm btn-success">
                            <i class="fas fa-download"></i> Télécharger
                        </a>
                    </div>
                </div>
                <div class="card-body text-center">
                    @if($content->type == 'image' && $content->file_path)
                        @php
                            $filePath = 'storage/' . $content->file_path;
                            $fileExists = file_exists(public_path($filePath));
                        @endphp
                        @if($fileExists)
                            <img src="{{ asset($filePath) }}" alt="{{ $content->title }}" 
                                class="img-fluid" style="max-height: 500px; box-shadow: 0 4px 8px rgba(0,0,0,0.1);">
                        @else
                            <div class="alert alert-warning">
                                <i class="fas fa-exclamation-triangle mr-2"></i> L'image n'est pas disponible sur le serveur.
                            </div>
                            <div class="text-center py-5">
                                <i class="fas fa-image fa-5x text-secondary mb-3"></i>
                            </div>
                        @endif
                    @elseif($content->type == 'video')
                        <video controls class="img-fluid" style="max-height: 500px;">
                            <source src="{{ asset('storage/' . $content->file_path) }}" type="video/mp4">
                            Votre navigateur ne supporte pas la lecture de vidéos.
                        </video>
                    @elseif($content->type == 'text' || $content->type == 'link')
                        <div class="text-center py-5">
                            @if($content->type == 'text')
                                <i class="fas fa-file-alt fa-5x text-secondary mb-3"></i>
                                <div class="mt-3 p-3 bg-light text-left">
                                    {!! nl2br(e($content->content_text)) !!}
                                </div>
                            @else
                                <i class="fas fa-link fa-5x text-secondary mb-3"></i>
                                <div class="mt-3">
                                    <a href="{{ $content->content_text }}" target="_blank" class="btn btn-info">
                                        <i class="fas fa-external-link-alt mr-1"></i> Ouvrir le lien
                                    </a>
                                    <p class="mt-3">{{ $content->content_text }}</p>
                                </div>
                            @endif
                        </div>
                    @elseif($content->file_path)
                        <div class="text-center py-5">
                            <i class="fas fa-file-alt fa-5x text-secondary mb-3"></i>
                            <p class="lead">{{ pathinfo($content->file_path, PATHINFO_EXTENSION) }} - {{ $content->file_size ? number_format($content->file_size / 1024, 2) . ' KB' : 'Taille inconnue' }}</p>
                            <p>Pour visualiser le contenu du fichier, veuillez le télécharger</p>
                        </div>
                    @else
                        <div class="alert alert-info">
                            Ce type de contenu ne peut pas être prévisualisé directement.
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Informations</h3>
                    <div class="card-tools">
                        <a href="{{ route('contents.edit', $content) }}" class="btn btn-sm btn-warning">
                            <i class="fas fa-edit"></i> Modifier
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4 text-bold">Titre:</div>
                        <div class="col-md-8">{{ $content->title }}</div>
                    </div>
                    <hr>
                    <div class="row">
                        <div class="col-md-4 text-bold">Statut:</div>
                        <div class="col-md-8">
                            @switch($content->status)
                                @case('draft')
                                    <span class="badge badge-warning">Brouillon</span>
                                    @break
                                @case('approved')
                                    <span class="badge badge-success">Approuvé</span>
                                    @break
                                @case('archived')
                                    <span class="badge badge-secondary">Archivé</span>
                                    @break
                                @default
                                    <span class="badge badge-secondary">{{ $content->status }}</span>
                            @endswitch
                        </div>
                    </div>
                    <hr>
                    <div class="row">
                        <div class="col-md-4 text-bold">Type:</div>
                        <div class="col-md-8">
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
                                @case('other')
                                    <span class="badge badge-secondary">Autre</span>
                                    @break
                                @default
                                    <span class="badge badge-secondary">{{ $content->type }}</span>
                            @endswitch
                        </div>
                    </div>
                    <hr>
                    <div class="row">
                        <div class="col-md-4 text-bold">Campagne:</div>
                        <div class="col-md-8">
                            @if($content->campaign_id && isset($campaign))
                                <a href="{{ route('campaigns.show', $campaign) }}">
                                    {{ $campaign->title ?? 'Campagne #'.$content->campaign_id }}
                                </a>
                            @elseif($content->campaign)
                                <a href="{{ route('campaigns.show', $content->campaign) }}">
                                    {{ $content->campaign->title }}
                                </a>
                            @else
                                Non associé
                            @endif
                        </div>
                    </div>
                    <hr>
                    <div class="row">
                        <div class="col-md-4 text-bold">Créé le:</div>
                        <div class="col-md-8">{{ $content->created_at->format('d/m/Y H:i') }}</div>
                    </div>
                    <hr>
                    <div class="row">
                        <div class="col-md-4 text-bold">Publication prévue:</div>
                        <div class="col-md-8">{{ $content->publication_date ? $content->publication_date->format('d/m/Y') : 'Non défini' }}</div>
                    </div>
                    <hr>
                    <div class="row">
                        <div class="col-md-4 text-bold">Taille:</div>
                        <div class="col-md-8">
                            @if($content->file_path)
                                @php
                                    $fileSize = 0;
                                    $filePath = storage_path('app/public/' . $content->file_path);
                                    if (file_exists($filePath)) {
                                        $fileSize = filesize($filePath);
                                    }
                                @endphp
                                {{ $fileSize ? number_format($fileSize / 1024, 2) . ' KB' : 'Inconnue' }}
                            @else
                                Non applicable
                            @endif
                        </div>
                    </div>
                    <hr>
                    <div class="row">
                        <div class="col-md-4 text-bold">Format:</div>
                        <div class="col-md-8">
                            @if($content->file_path)
                                {{ strtoupper(pathinfo($content->file_path, PATHINFO_EXTENSION)) }}
                            @else
                                Non applicable
                            @endif
                        </div>
                    </div>
                    <hr>
                    <div class="row">
                        <div class="col-md-4 text-bold">Tags:</div>
                        <div class="col-md-8">
                            @php
                                // Vérifier si le champ existe dans la base de données et contient des données
                                $hasTags = false;
                                if (Schema::hasColumn('contents', 'tags') && !empty($content->tags)) {
                                    $hasTags = true;
                                    $tagsList = explode(',', $content->tags);
                                }
                            @endphp
                            
                            @if($hasTags)
                                @foreach($tagsList as $tag)
                                    <span class="badge badge-primary">{{ trim($tag) }}</span>
                                @endforeach
                            @else
                                Aucun tag
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <div class="card mt-3">
                <div class="card-header">
                    <h3 class="card-title">Description</h3>
                </div>
                <div class="card-body">
                    {{ $content->description ?: 'Aucune description fournie.' }}
                </div>
            </div>

            <div class="card mt-3">
                <div class="card-header">
                    <h3 class="card-title">Directives de publication</h3>
                </div>
                <div class="card-body">
                    @if($content->guidelines)
                        <ul class="list-group list-group-flush">
                            @foreach((array)$content->guidelines as $guideline)
                                <li class="list-group-item">{{ $guideline }}</li>
                            @endforeach
                        </ul>
                    @else
                        <p>Aucune directive spécifiée.</p>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Publications associées -->
    <div class="row mt-4">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Publications associées</h3>
                </div>
                <div class="card-body">
                    <table class="table table-bordered table-striped">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Plateforme</th>
                                <th>Influenceur</th>
                                <th>Date de publication</th>
                                <th>Statut</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($content->publications ?? [] as $publication)
                            <tr>
                                <td>{{ $publication->id }}</td>
                                <td>
                                    @switch($publication->platform)
                                        @case('instagram')
                                            <i class="fab fa-instagram text-danger"></i>
                                            @break
                                        @case('facebook')
                                            <i class="fab fa-facebook text-primary"></i>
                                            @break
                                        @case('twitter')
                                            <i class="fab fa-twitter text-info"></i>
                                            @break
                                        @case('tiktok')
                                            <i class="fab fa-tiktok text-dark"></i>
                                            @break
                                        @case('youtube')
                                            <i class="fab fa-youtube text-danger"></i>
                                            @break
                                        @default
                                            <i class="fas fa-globe text-secondary"></i>
                                    @endswitch
                                    {{ ucfirst($publication->platform) }}
                                </td>
                                <td>{{ $publication->influencer->name ?? 'N/A' }}</td>
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
                                    <a href="{{ route('publications.show', $publication) }}" class="btn btn-sm btn-info">
                                        <i class="fas fa-eye"></i> Détails
                                    </a>
                                    <a href="{{ $publication->url }}" target="_blank" class="btn btn-sm btn-primary">
                                        <i class="fas fa-external-link-alt"></i> Voir
                                    </a>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="6" class="text-center">Aucune publication n'utilise ce contenu</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
