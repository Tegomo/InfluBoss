@extends('layouts.adminlte')

@section('title', 'Modifier le contenu')

@section('content_header')
<div class="container-fluid">
    <div class="row mb-2">
        <div class="col-sm-6">
            <h1>Modifier le contenu</h1>
        </div>
        <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="{{ route('contents.index') }}">Contenus</a></li>
                <li class="breadcrumb-item active">Modifier</li>
            </ol>
        </div>
    </div>
</div>
@endsection

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-8">
            <div class="card card-warning">
                <div class="card-header">
                    <h3 class="card-title">Modification de "{{ $content->title }}"</h3>
                </div>
                
                <form method="POST" action="{{ route('contents.update', $content) }}" enctype="multipart/form-data">
                    @csrf
                    @method('PUT')
                    <!-- Champ caché pour le type de contenu -->
                    <input type="hidden" name="type" value="{{ $content->type }}">
                    <div class="card-body">
                        <div class="form-group">
                            <label for="title">Titre <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('title') is-invalid @enderror" 
                                id="title" name="title" value="{{ old('title', $content->title) }}" required>
                            @error('title')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                        
                        <div class="form-group">
                            <label for="campaign_id">Campagne <span class="text-danger">*</span></label>
                            <select class="form-control @error('campaign_id') is-invalid @enderror" 
                                id="campaign_id" name="campaign_id" required>
                                <option value="">Sélectionner une campagne</option>
                                @foreach($campaigns as $campaign)
                                    <option value="{{ $campaign->id }}" {{ old('campaign_id', $content->campaign_id) == $campaign->id ? 'selected' : '' }}>
                                        {{ $campaign->name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('campaign_id')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                        
                        <div class="form-group">
                            <label for="description">Description</label>
                            <textarea class="form-control @error('description') is-invalid @enderror" 
                                id="description" name="description" rows="3">{{ old('description', $content->description) }}</textarea>
                            @error('description')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                        
                        <div class="form-group">
                            <label>Type de contenu actuel</label>
                            <div>
                                @switch($content->type)
                                    @case('image')
                                        <span class="badge badge-info">Image</span>
                                        @break
                                    @case('video')
                                        <span class="badge badge-danger">Vidéo</span>
                                        @break
                                    @case('text')
                                        <span class="badge badge-primary">Texte</span>
                                        @break
                                    @case('link')
                                        <span class="badge badge-success">Lien</span>
                                        @break
                                    @case('other')
                                        <span class="badge badge-secondary">Autre</span>
                                        @break
                                    @default
                                        <span class="badge badge-secondary">{{ $content->type }}</span>
                                @endswitch
                            </div>
                            <small class="form-text text-muted">Le type de contenu ne peut pas être modifié</small>
                        </div>
                        
                        <div class="form-group">
                            <label for="current_file">Fichier actuel</label>
                            <div class="mb-2">
                                @if($content->type == 'image')
                                    <img src="{{ asset('storage/' . $content->file_path) }}" alt="{{ $content->title }}" 
                                        style="max-height: 150px; max-width: 300px;" class="img-thumbnail">
                                @elseif($content->type == 'video')
                                    <div><i class="fas fa-video fa-2x"></i> {{ basename($content->file_path) }}</div>
                                @elseif($content->type == 'text')
                                    <div><i class="fas fa-file-alt fa-2x"></i> {{ Str::limit($content->content_text, 50) }}</div>
                                @elseif($content->type == 'link')
                                    <div><i class="fas fa-link fa-2x"></i> {{ $content->content_text }}</div>
                                @else
                                    <div><i class="fas fa-file fa-2x"></i> {{ basename($content->file_path ?? '') }}</div>
                                @endif
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label for="file">Remplacer le fichier</label>
                            <div class="input-group">
                                <div class="custom-file">
                                    <input type="file" class="custom-file-input @error('file') is-invalid @enderror" 
                                        id="file" name="file">
                                    <label class="custom-file-label" for="file">Choisir un fichier</label>
                                </div>
                            </div>
                            <small class="form-text text-muted">
                                Laissez ce champ vide pour conserver le fichier actuel
                            </small>
                            @error('file')
                                <span class="invalid-feedback d-block">{{ $message }}</span>
                            @enderror
                        </div>
                        
                        <div class="form-group">
                            <label for="platform_guidelines">Directives de publication</label>
                            <textarea class="form-control @error('platform_guidelines') is-invalid @enderror" 
                                id="platform_guidelines" name="platform_guidelines" rows="3">{{ old('platform_guidelines', $content->platform_guidelines) }}</textarea>
                            @error('platform_guidelines')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                        
                        <div class="form-group">
                            <label for="tags">Tags</label>
                            <input type="text" class="form-control @error('tags') is-invalid @enderror" 
                                id="tags" name="tags" value="{{ old('tags', $content->tags) }}" 
                                placeholder="Séparez les tags par des virgules">
                            <small class="form-text text-muted">
                                Exemple: #orange, #digital, #influenceur
                            </small>
                            @error('tags')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                    
                    <div class="card-footer">
                        <button type="submit" class="btn btn-warning">Mettre à jour</button>
                        <a href="{{ route('contents.show', $content) }}" class="btn btn-secondary">Annuler</a>
                    </div>
                </form>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Informations</h3>
                </div>
                <div class="card-body">
                    <p><strong>ID:</strong> {{ $content->id }}</p>
                    <p><strong>Créé le:</strong> {{ $content->created_at->format('d/m/Y H:i') }}</p>
                    <p><strong>Dernière modification:</strong> {{ $content->updated_at->format('d/m/Y H:i') }}</p>
                    <p><strong>Format:</strong> {{ pathinfo($content->file_path, PATHINFO_EXTENSION) }}</p>
                    <p><strong>Taille:</strong> {{ $content->file_size ? number_format($content->file_size / 1024, 2) . ' KB' : 'Inconnue' }}</p>
                    
                    <div class="alert alert-info">
                        <i class="icon fas fa-info-circle"></i> Les modifications apporteront des changements à toutes les missions et publications associées.
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    // Script pour afficher le nom du fichier sélectionné dans le champ input file
    document.addEventListener('DOMContentLoaded', function() {
        document.querySelector('.custom-file-input').addEventListener('change', function(e) {
            var fileName = e.target.files[0].name;
            var nextSibling = e.target.nextElementSibling;
            nextSibling.innerText = fileName;
        });
    });
</script>
@endpush
