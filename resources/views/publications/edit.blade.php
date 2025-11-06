@extends('layouts.adminlte')

@section('title', 'Modifier la publication')

@section('content_header')
<div class="container-fluid">
    <div class="row mb-2">
        <div class="col-sm-6">
            <h1>Modifier la publication</h1>
        </div>
        <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="{{ route('publications.index') }}">Publications</a></li>
                <li class="breadcrumb-item active">Modifier</li>
            </ol>
        </div>
    </div>
</div>
@endsection

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-12">
            <div class="card card-warning">
                <div class="card-header">
                    <h3 class="card-title">Modification de la publication #{{ $publication->id }}</h3>
                </div>
                <form method="POST" action="{{ route('publications.update', $publication) }}" enctype="multipart/form-data">
                    @csrf
                    @method('PUT')
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="assignment_id">Mission <span class="text-danger">*</span></label>
                                    <select class="form-control @error('assignment_id') is-invalid @enderror" 
                                        id="assignment_id" name="assignment_id" required>
                                        <option value="">Sélectionner une mission</option>
                                        @foreach($assignments as $assignment)
                                            <option value="{{ $assignment->id }}" {{ old('assignment_id', $publication->assignment_id) == $assignment->id ? 'selected' : '' }}
                                                data-influencer-id="{{ $assignment->influencer_id }}"
                                                data-campaign-id="{{ $assignment->campaign_id }}">
                                                Mission #{{ $assignment->id }} - {{ $assignment->campaign->title ?? 'N/A' }} 
                                                ({{ $assignment->influencer->name ?? 'N/A' }})
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('assignment_id')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="platform">Plateforme <span class="text-danger">*</span></label>
                                    <select class="form-control @error('platform') is-invalid @enderror" 
                                        id="platform" name="platform" required>
                                        <option value="">Sélectionner une plateforme</option>
                                        <option value="instagram" {{ old('platform', $publication->platform) == 'instagram' ? 'selected' : '' }}>Instagram</option>
                                        <option value="facebook" {{ old('platform', $publication->platform) == 'facebook' ? 'selected' : '' }}>Facebook</option>
                                        <option value="twitter" {{ old('platform', $publication->platform) == 'twitter' ? 'selected' : '' }}>Twitter</option>
                                        <option value="tiktok" {{ old('platform', $publication->platform) == 'tiktok' ? 'selected' : '' }}>TikTok</option>
                                        <option value="youtube" {{ old('platform', $publication->platform) == 'youtube' ? 'selected' : '' }}>YouTube</option>
                                        <option value="linkedin" {{ old('platform', $publication->platform) == 'linkedin' ? 'selected' : '' }}>LinkedIn</option>
                                    </select>
                                    @error('platform')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label for="post_url">URL de la publication <span class="text-danger">*</span></label>
                            <input type="url" class="form-control @error('post_url') is-invalid @enderror" 
                                id="post_url" name="post_url" value="{{ old('post_url', $publication->post_url) }}" required>
                            <small class="form-text text-muted">
                                URL directe vers la publication sur la plateforme sociale
                            </small>
                            @error('post_url')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                        
                        <div class="form-group">
                            <label for="published_at">Date de publication <span class="text-danger">*</span></label>
                            <input type="datetime-local" class="form-control @error('published_at') is-invalid @enderror" 
                                id="published_at" name="published_at" value="{{ old('published_at', $publication->published_at ? $publication->published_at->format('Y-m-d\TH:i') : '') }}" required>
                            @error('published_at')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                        
                        <div class="form-group">
                            <label for="content">Contenu de la publication</label>
                            <textarea class="form-control @error('content') is-invalid @enderror" 
                                id="content" name="content" rows="4">{{ old('content', $publication->content) }}</textarea>
                            <small class="form-text text-muted">
                                Texte de la publication tel qu'il apparaît sur la plateforme
                            </small>
                            @error('content')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                        
                        <div class="form-group">
                            <label for="screenshot">Capture d'écran</label>
                            
                            @if($publication->screenshot_path)
                                <div class="mb-2">
                                    <img src="{{ asset('storage/' . $publication->screenshot_path) }}" 
                                         alt="Capture d'écran actuelle" class="img-thumbnail" style="max-height: 150px;">
                                    <p class="text-muted mt-1">Capture d'écran actuelle</p>
                                </div>
                                
                                <div class="custom-control custom-checkbox mb-2">
                                    <input type="checkbox" class="custom-control-input" id="remove_screenshot" name="remove_screenshot">
                                    <label class="custom-control-label" for="remove_screenshot">Supprimer cette capture d'écran</label>
                                </div>
                            @endif
                            
                            <div class="input-group">
                                <div class="custom-file">
                                    <input type="file" class="custom-file-input @error('screenshot') is-invalid @enderror" 
                                        id="screenshot" name="screenshot" accept="image/*">
                                    <label class="custom-file-label" for="screenshot">
                                        {{ $publication->screenshot_path ? 'Remplacer la capture' : 'Choisir un fichier' }}
                                    </label>
                                </div>
                            </div>
                            <small class="form-text text-muted">
                                Capture d'écran de la publication (JPG, PNG - max 5MB)
                            </small>
                            @error('screenshot')
                                <span class="invalid-feedback d-block">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="embed_code">Code d'intégration</label>
                            <textarea class="form-control @error('embed_code') is-invalid @enderror" 
                                id="embed_code" name="embed_code" rows="3">{{ old('embed_code', $publication->embed_code) }}</textarea>
                            <small class="form-text text-muted">
                                Code d'intégration fourni par la plateforme pour afficher la publication dans un iframe
                            </small>
                            @error('embed_code')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                        
                        <div class="form-group">
                            <label>Contenus utilisés</label>
                            <div class="select2-orange">
                                <select class="form-control" multiple id="contents" name="contents[]">
                                    @php
                                        $currentContentIds = old('contents', $publication->contents ? $publication->contents->pluck('id')->toArray() : []);
                                    @endphp
                                    @foreach($availableContents as $content)
                                        <option value="{{ $content->id }}" {{ in_array($content->id, $currentContentIds) ? 'selected' : '' }}
                                                data-campaign-id="{{ $content->campaign_id }}">
                                            {{ $content->title }} ({{ ucfirst($content->content_type) }})
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <small class="form-text text-muted">
                                Sélectionnez les contenus de la campagne utilisés dans cette publication
                            </small>
                        </div>
                        
                        <div class="card card-orange card-outline">
                            <div class="card-header">
                                <h3 class="card-title">Statistiques actuelles</h3>
                            </div>
                            <div class="card-body">
                                <div class="alert alert-info">
                                    <i class="icon fas fa-info-circle"></i> La modification des statistiques créera une nouvelle entrée dans l'historique des performances. Pour une mise à jour complète, utilisez plutôt la fonction "Rafraîchir" depuis la page détaillée.
                                </div>
                                
                                <div class="row">
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label for="likes">Nombre de j'aime</label>
                                            <input type="number" min="0" class="form-control @error('likes') is-invalid @enderror" 
                                                id="likes" name="likes" value="{{ old('likes', $publication->latestStats ? $publication->latestStats->likes : 0) }}">
                                            @error('likes')
                                                <span class="invalid-feedback">{{ $message }}</span>
                                            @enderror
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label for="comments">Nombre de commentaires</label>
                                            <input type="number" min="0" class="form-control @error('comments') is-invalid @enderror" 
                                                id="comments" name="comments" value="{{ old('comments', $publication->latestStats ? $publication->latestStats->comments : 0) }}">
                                            @error('comments')
                                                <span class="invalid-feedback">{{ $message }}</span>
                                            @enderror
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label for="shares">Nombre de partages</label>
                                            <input type="number" min="0" class="form-control @error('shares') is-invalid @enderror" 
                                                id="shares" name="shares" value="{{ old('shares', $publication->latestStats ? $publication->latestStats->shares : 0) }}">
                                            @error('shares')
                                                <span class="invalid-feedback">{{ $message }}</span>
                                            @enderror
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="views">Nombre de vues (si applicable)</label>
                                            <input type="number" min="0" class="form-control @error('views') is-invalid @enderror" 
                                                id="views" name="views" value="{{ old('views', $publication->latestStats ? $publication->latestStats->views : 0) }}">
                                            @error('views')
                                                <span class="invalid-feedback">{{ $message }}</span>
                                            @enderror
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="engagement_rate">Taux d'engagement (%)</label>
                                            <input type="number" step="0.01" min="0" class="form-control @error('engagement_rate') is-invalid @enderror" 
                                                id="engagement_rate" name="engagement_rate" value="{{ old('engagement_rate', $publication->latestStats ? $publication->latestStats->engagement_rate : 0) }}">
                                            @error('engagement_rate')
                                                <span class="invalid-feedback">{{ $message }}</span>
                                            @enderror
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label for="status">Statut <span class="text-danger">*</span></label>
                            <select class="form-control @error('status') is-invalid @enderror" 
                                id="status" name="status" required>
                                <option value="pending" {{ old('status', $publication->status) == 'pending' ? 'selected' : '' }}>En attente</option>
                                <option value="verified" {{ old('status', $publication->status) == 'verified' ? 'selected' : '' }}>Vérifiée</option>
                                <option value="rejected" {{ old('status', $publication->status) == 'rejected' ? 'selected' : '' }}>Rejetée</option>
                            </select>
                            @error('status')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                    
                    <div class="card-footer">
                        <button type="submit" class="btn btn-warning">Mettre à jour</button>
                        <a href="{{ route('publications.show', $publication) }}" class="btn btn-secondary">Annuler</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Afficher le nom du fichier sélectionné dans le champ input file
        document.querySelector('.custom-file-input').addEventListener('change', function(e) {
            var fileName = e.target.files[0].name;
            var nextSibling = e.target.nextElementSibling;
            nextSibling.innerText = fileName;
        });
        
        // Si disponible, initialiser Select2 pour améliorer l'UX des listes déroulantes
        if (typeof $ !== 'undefined' && $.fn.select2) {
            $('#assignment_id').select2({
                placeholder: 'Sélectionner une mission',
                allowClear: true
            });
            
            $('#platform').select2({
                placeholder: 'Sélectionner une plateforme',
                allowClear: true
            });
            
            $('#contents').select2({
                placeholder: 'Sélectionner les contenus utilisés',
                allowClear: true
            });
            
            // Filtrer les contenus en fonction de la campagne sélectionnée
            $('#assignment_id').on('change', function() {
                var campaignId = $('option:selected', this).data('campaign-id');
                
                if (campaignId) {
                    $('#contents option').each(function() {
                        if ($(this).data('campaign-id') == campaignId) {
                            $(this).prop('disabled', false);
                        } else {
                            $(this).prop('disabled', true);
                        }
                    });
                } else {
                    $('#contents option').prop('disabled', false);
                }
                
                // Ne pas réinitialiser la sélection lors de l'édition
                // car les contenus peuvent déjà être sélectionnés
            });
            
            // Déclencher le changement initial pour filtrer les contenus
            $('#assignment_id').trigger('change');
        }
    });
</script>
@endpush
