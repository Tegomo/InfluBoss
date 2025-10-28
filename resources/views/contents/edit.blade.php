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
                        
                        @if($content->type == 'text' || $content->type == 'link')
                        <div class="form-group">
                            <label for="content_text">Contenu {{ $content->type == 'text' ? 'textuel' : 'du lien' }}</label>
                            <textarea class="form-control @error('content_text') is-invalid @enderror" 
                                id="content_text" name="content_text" rows="3">{{ old('content_text', $content->content_text) }}</textarea>
                            @error('content_text')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                        @endif
                        
                        <div class="form-group">
                            <label for="guidelines">Directives de publication</label>
                            <div id="guidelines-container">
                                @if(old('guidelines', json_decode($content->guidelines ?? '[]')))
                                    @foreach(old('guidelines', json_decode($content->guidelines ?? '[]')) as $index => $guideline)
                                        <div class="input-group mb-2">
                                            <input type="text" class="form-control" name="guidelines[]" value="{{ $guideline }}" placeholder="Directive de publication">
                                            <div class="input-group-append">
                                                <button type="button" class="btn btn-danger remove-guideline"><i class="fas fa-minus"></i></button>
                                            </div>
                                        </div>
                                    @endforeach
                                @else
                                    <div class="input-group mb-2">
                                        <input type="text" class="form-control" name="guidelines[]" placeholder="Directive de publication">
                                        <div class="input-group-append">
                                            <button type="button" class="btn btn-danger remove-guideline"><i class="fas fa-minus"></i></button>
                                        </div>
                                    </div>
                                @endif
                            </div>
                            <button type="button" class="btn btn-sm btn-primary mt-2" id="add-guideline">Ajouter une directive</button>
                            @error('guidelines')
                                <span class="invalid-feedback d-block">{{ $message }}</span>
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
                        
                        <div class="form-group">
                            <label for="status">Statut <span class="text-danger">*</span></label>
                            <select class="form-control @error('status') is-invalid @enderror" 
                                id="status" name="status" required>
                                <option value="draft" {{ old('status', $content->status) == 'draft' ? 'selected' : '' }}>Brouillon</option>
                                <option value="approved" {{ old('status', $content->status) == 'approved' ? 'selected' : '' }}>Approuvé</option>
                                <option value="archived" {{ old('status', $content->status) == 'archived' ? 'selected' : '' }}>Archivé</option>
                            </select>
                            @error('status')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                        
                        <div class="form-group">
                            <label for="publication_date">Date de publication</label>
                            <input type="date" class="form-control @error('publication_date') is-invalid @enderror" 
                                id="publication_date" name="publication_date" 
                                value="{{ old('publication_date', $content->publication_date ? $content->publication_date->format('Y-m-d') : '') }}">
                            @error('publication_date')
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
        
        // Script pour ajouter/supprimer des directives de publication
        document.getElementById('add-guideline').addEventListener('click', function() {
            var container = document.getElementById('guidelines-container');
            var newRow = document.createElement('div');
            newRow.className = 'input-group mb-2';
            newRow.innerHTML = `
                <input type="text" class="form-control" name="guidelines[]" placeholder="Directive de publication">
                <div class="input-group-append">
                    <button type="button" class="btn btn-danger remove-guideline"><i class="fas fa-minus"></i></button>
                </div>
            `;
            container.appendChild(newRow);
            
            // Ajouter l'événement de suppression au nouveau bouton
            newRow.querySelector('.remove-guideline').addEventListener('click', removeGuideline);
        });
        
        // Ajouter l'événement de suppression aux boutons existants
        document.querySelectorAll('.remove-guideline').forEach(function(button) {
            button.addEventListener('click', removeGuideline);
        });
        
        function removeGuideline() {
            var row = this.closest('.input-group');
            if (document.querySelectorAll('#guidelines-container .input-group').length > 1) {
                row.remove();
            } else {
                // Si c'est la dernière ligne, vider le champ plutôt que de supprimer
                row.querySelector('input').value = '';
            }
        }
    });
</script>
@endpush
