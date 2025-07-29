@extends('layouts.adminlte')

@section('title', 'Créer un contenu')

@section('content_header')
<div class="container-fluid">
    <div class="row mb-2">
        <div class="col-sm-6">
            <h1>Créer un contenu</h1>
        </div>
        <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="{{ route('contents.index') }}">Contenus</a></li>
                <li class="breadcrumb-item active">Créer</li>
            </ol>
        </div>
    </div>
</div>
@endsection

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-12">
            <div class="card card-primary">
                <div class="card-header">
                    <h3 class="card-title">Informations du contenu</h3>
                </div>
                
                <form method="POST" action="{{ route('contents.store') }}" enctype="multipart/form-data">
                    @csrf
                    <div class="card-body">
                        <div class="form-group">
                            <label for="title">Titre <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('title') is-invalid @enderror" 
                                id="title" name="title" value="{{ old('title') }}" required>
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
                                    <option value="{{ $campaign->id }}" {{ old('campaign_id') == $campaign->id || request('campaign_id') == $campaign->id ? 'selected' : '' }}>
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
                                id="description" name="description" rows="3">{{ old('description') }}</textarea>
                            @error('description')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                        
                        <div class="form-group">
                            <label for="type">Type de contenu <span class="text-danger">*</span></label>
                            <select class="form-control @error('type') is-invalid @enderror" 
                                id="type" name="type" required>
                                <option value="">Sélectionner un type</option>
                                <option value="image" {{ old('type') == 'image' ? 'selected' : '' }}>Image</option>
                                <option value="video" {{ old('type') == 'video' ? 'selected' : '' }}>Vidéo</option>
                                <option value="text" {{ old('type') == 'text' ? 'selected' : '' }}>Texte</option>
                                <option value="link" {{ old('type') == 'link' ? 'selected' : '' }}>Lien</option>
                                <option value="other" {{ old('type') == 'other' ? 'selected' : '' }}>Autre</option>
                            </select>
                            @error('type')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                        
                        <div id="file-group" class="form-group">
                            <label for="file">Fichier <span class="text-danger file-required">*</span></label>
                            <div class="input-group">
                                <div class="custom-file">
                                    <input type="file" class="custom-file-input @error('file') is-invalid @enderror" 
                                        id="file" name="file">
                                    <label class="custom-file-label" for="file">Choisir un fichier</label>
                                </div>
                            </div>
                            <small class="form-text text-muted">
                                Images: JPG, PNG, GIF (max 5MB) | Vidéos: MP4, MOV (max 20MB)
                            </small>
                            @error('file')
                                <span class="invalid-feedback d-block">{{ $message }}</span>
                            @enderror
                        </div>

                        <div id="content-text-group" class="form-group" style="display: none;">
                            <label for="content_text">Contenu texte <span class="text-danger text-required">*</span></label>
                            <textarea class="form-control @error('content_text') is-invalid @enderror" 
                                id="content_text" name="content_text" rows="3">{{ old('content_text') }}</textarea>
                            <small class="form-text text-muted">
                                Texte ou URL selon le type de contenu sélectionné.
                            </small>
                            @error('content_text')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                        
                        <div class="form-group">
                            <label for="publication_date">Date de publication prévue</label>
                            <input type="date" class="form-control @error('publication_date') is-invalid @enderror" 
                                id="publication_date" name="publication_date" value="{{ old('publication_date') }}">
                            @error('publication_date')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                        
                        <div class="form-group">
                            <label for="status">Statut <span class="text-danger">*</span></label>
                            <select class="form-control @error('status') is-invalid @enderror" 
                                id="status" name="status" required>
                                <option value="draft" {{ old('status') == 'draft' ? 'selected' : '' }}>Brouillon</option>
                                <option value="approved" {{ old('status') == 'approved' ? 'selected' : '' }}>Approuvé</option>
                                <option value="archived" {{ old('status') == 'archived' ? 'selected' : '' }}>Archivé</option>
                            </select>
                            @error('status')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="guidelines">Directives de publication</label>
                            <textarea class="form-control @error('guidelines') is-invalid @enderror" 
                                id="guidelines" name="guidelines[]" rows="3">{{ old('guidelines') ? old('guidelines')[0] : '' }}</textarea>
                            <small class="form-text text-muted">
                                Instructions spécifiques pour la publication de ce contenu sur les réseaux sociaux.
                            </small>
                            @error('guidelines')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                        
                        <div class="form-group">
                            <label>Tags</label>
                            <input type="text" class="form-control @error('tags') is-invalid @enderror" 
                                id="tags" name="tags" value="{{ old('tags') }}" 
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
                        <button type="submit" class="btn btn-primary">Créer</button>
                        <a href="{{ route('contents.index') }}" class="btn btn-secondary">Annuler</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    // Script pour afficher le nom du fichier sélectionné dans le champ input file
    document.addEventListener('DOMContentLoaded', function() {
        // Gestion de l'affichage du nom de fichier
        const fileInput = document.querySelector('.custom-file-input');
        if (fileInput) {
            fileInput.addEventListener('change', function(e) {
                if (e.target.files.length > 0) {
                    var fileName = e.target.files[0].name;
                    var nextSibling = e.target.nextElementSibling;
                    nextSibling.innerText = fileName;
                }
            });
        }
        
        // Gestion de l'affichage conditionnel des champs selon le type
        const typeSelect = document.getElementById('type');
        const fileGroup = document.getElementById('file-group');
        const contentTextGroup = document.getElementById('content-text-group');
        const fileInput2 = document.getElementById('file');
        const contentTextInput = document.getElementById('content_text');
        
        function updateFieldVisibility() {
            const selectedType = typeSelect.value;
            
            if (selectedType === 'text' || selectedType === 'link') {
                // Pour les types text et link, montrer le champ content_text et cacher file
                fileGroup.style.display = 'none';
                contentTextGroup.style.display = 'block';
                fileInput2.removeAttribute('required');
                contentTextInput.setAttribute('required', 'required');
            } else if (selectedType === 'image' || selectedType === 'video') {
                // Pour les types image et video, montrer le champ file et cacher content_text
                fileGroup.style.display = 'block';
                contentTextGroup.style.display = 'none';
                fileInput2.setAttribute('required', 'required');
                contentTextInput.removeAttribute('required');
            } else if (selectedType === 'other') {
                // Pour le type other, montrer les deux champs mais sans obligation
                fileGroup.style.display = 'block';
                contentTextGroup.style.display = 'block';
                fileInput2.removeAttribute('required');
                contentTextInput.removeAttribute('required');
            } else {
                // Si aucun type n'est sélectionné, cacher les deux
                fileGroup.style.display = 'none';
                contentTextGroup.style.display = 'none';
            }
        }
        
        // Initialiser l'affichage au chargement de la page
        if (typeSelect) {
            updateFieldVisibility();
            
            // Mettre à jour à chaque changement de type
            typeSelect.addEventListener('change', updateFieldVisibility);
        }
    });
</script>
@endpush
