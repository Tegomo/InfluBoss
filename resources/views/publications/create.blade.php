@extends('layouts.adminlte')

@section('title', 'Ajouter une publication')

@section('content_header')
<div class="container-fluid">
    <div class="row mb-2">
        <div class="col-sm-6">
            <h1>Ajouter une publication</h1>
        </div>
        <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="{{ route('publications.index') }}">Publications</a></li>
                <li class="breadcrumb-item active">Ajouter</li>
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
                    <h3 class="card-title">Informations de la publication</h3>
                </div>
                
                <form method="POST" action="{{ route('publications.store') }}" enctype="multipart/form-data">
                    @csrf
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="assignment_id">Mission <span class="text-danger">*</span></label>
                                    <select class="form-control @error('assignment_id') is-invalid @enderror" 
                                        id="assignment_id" name="assignment_id" required>
                                        <option value="">Sélectionner une mission</option>
                                        @foreach($assignments as $assignment)
                                            <option value="{{ $assignment->id }}" {{ old('assignment_id') == $assignment->id || request('assignment_id') == $assignment->id ? 'selected' : '' }}
                                                data-influencer-id="{{ $assignment->influencer_id }}"
                                                data-campaign-id="{{ $assignment->campaign_id }}">
                                                Mission #{{ $assignment->id }} - {{ $assignment->campaign->name ?? 'N/A' }} 
                                                ({{ $assignment->influencer->name ?? 'N/A' }})
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('assignment_id')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                                @include('publications.partials.deliverable-select', [
                                    'selectedDeliverableId' => old('deliverable_id', request('deliverable_id')),
                                ])
                            </div>
                            
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="platform">Plateforme <span class="text-danger">*</span></label>
                                    <select class="form-control @error('platform') is-invalid @enderror" 
                                        id="platform" name="platform" required>
                                        <option value="">Sélectionner une plateforme</option>
                                        <option value="instagram" {{ old('platform') == 'instagram' ? 'selected' : '' }}>Instagram</option>
                                        <option value="facebook" {{ old('platform') == 'facebook' ? 'selected' : '' }}>Facebook</option>
                                        <option value="twitter" {{ old('platform') == 'twitter' ? 'selected' : '' }}>Twitter</option>
                                        <option value="tiktok" {{ old('platform') == 'tiktok' ? 'selected' : '' }}>TikTok</option>
                                        <option value="youtube" {{ old('platform') == 'youtube' ? 'selected' : '' }}>YouTube</option>
                                        <option value="linkedin" {{ old('platform') == 'linkedin' ? 'selected' : '' }}>LinkedIn</option>
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
                                id="post_url" name="post_url" value="{{ old('post_url') }}" required 
                                placeholder="https://www.instagram.com/p/...">
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
                                id="published_at" name="published_at" value="{{ old('published_at') }}" required>
                            @error('published_at')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                        
                        <div class="form-group">
                            <label for="post_type">Type de publication <span class="text-danger">*</span></label>
                            <select class="form-control @error('post_type') is-invalid @enderror" 
                                id="post_type" name="post_type" required>
                                <option value="">Sélectionner un type</option>
                                <option value="post" {{ old('post_type') == 'post' ? 'selected' : '' }}>Post</option>
                                <option value="story" {{ old('post_type') == 'story' ? 'selected' : '' }}>Story</option>
                                <option value="reel" {{ old('post_type') == 'reel' ? 'selected' : '' }}>Reel</option>
                                <option value="video" {{ old('post_type') == 'video' ? 'selected' : '' }}>Video</option>
                                <option value="short" {{ old('post_type') == 'short' ? 'selected' : '' }}>Short</option>
                                <option value="article" {{ old('post_type') == 'article' ? 'selected' : '' }}>Article</option>
                            </select>
                            @error('post_type')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                        
                        <div class="form-group">
                            <label for="post_content">Contenu de la publication</label>
                            <textarea class="form-control @error('post_content') is-invalid @enderror" 
                                id="post_content" name="post_content" rows="4">{{ old('post_content') }}</textarea>
                            <small class="form-text text-muted">
                                Texte de la publication tel qu'il apparaît sur la plateforme
                            </small>
                            @error('post_content')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                        
                        <div class="form-group">
                            <label for="screenshot">Capture d'écran (optionnel)</label>
                            <div class="input-group">
                                <div class="custom-file">
                                    <input type="file" class="custom-file-input @error('screenshot') is-invalid @enderror" 
                                        id="screenshot" name="screenshot" accept="image/*">
                                    <label class="custom-file-label" for="screenshot">Choisir un fichier</label>
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
                            <label for="embed_code">Code d'intégration (optionnel)</label>
                            <textarea class="form-control @error('embed_code') is-invalid @enderror" 
                                id="embed_code" name="embed_code" rows="3">{{ old('embed_code') }}</textarea>
                            <small class="form-text text-muted">
                                Code d'intégration fourni par la plateforme pour afficher la publication dans un iframe
                            </small>
                            @error('embed_code')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                        
                        <div class="form-group">
                            <label for="content_id">Contenu principal utilisé (optionnel)</label>
                            <div class="select2-orange">
                                <select class="form-control" id="content_id" name="content_id">
                                    <option value="">Aucun contenu spécifique</option>
                                    @foreach($contents as $content)
                                        <option value="{{ $content->id }}" {{ old('content_id') == $content->id ? 'selected' : '' }}
                                                data-campaign-id="{{ $content->campaign_id }}">
                                            {{ $content->title ?? 'Sans titre' }} ({{ ucfirst($content->type) }})
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <small class="form-text text-muted">
                                Sélectionnez le contenu principal de la campagne utilisé dans cette publication
                            </small>
                            @error('content_id')
                                <span class="invalid-feedback d-block">{{ $message }}</span>
                            @enderror
                        </div>
                        
                        <div class="form-group">
                            <label for="contents">Contenus additionnels utilisés (optionnel)</label>
                            <div class="select2-orange">
                                <select class="form-control" multiple id="contents" name="contents[]">
                                    @foreach($contents as $content)
                                        <option value="{{ $content->id }}" {{ in_array($content->id, old('contents', [])) ? 'selected' : '' }}
                                                data-campaign-id="{{ $content->campaign_id }}">
                                            {{ $content->title ?? 'Sans titre' }} ({{ ucfirst($content->type) }})
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <small class="form-text text-muted">
                                Sélectionnez les contenus additionnels de la campagne utilisés dans cette publication
                            </small>
                        </div>
                        
                        <div class="card card-orange card-outline">
                            <div class="card-header">
                                <h3 class="card-title">Statistiques initiales</h3>
                            </div>
                            <div class="card-body">
                                <p class="text-muted">Ces données seront utilisées comme point de référence initial pour suivre l'évolution de la publication</p>
                                
                                <div class="row">
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label for="likes">Nombre de j'aime</label>
                                            <input type="number" min="0" class="form-control @error('likes') is-invalid @enderror" 
                                                id="likes" name="likes" value="{{ old('likes', 0) }}">
                                            @error('likes')
                                                <span class="invalid-feedback">{{ $message }}</span>
                                            @enderror
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label for="comments">Nombre de commentaires</label>
                                            <input type="number" min="0" class="form-control @error('comments') is-invalid @enderror" 
                                                id="comments" name="comments" value="{{ old('comments', 0) }}">
                                            @error('comments')
                                                <span class="invalid-feedback">{{ $message }}</span>
                                            @enderror
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label for="shares">Nombre de partages</label>
                                            <input type="number" min="0" class="form-control @error('shares') is-invalid @enderror" 
                                                id="shares" name="shares" value="{{ old('shares', 0) }}">
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
                                                id="views" name="views" value="{{ old('views', 0) }}">
                                            @error('views')
                                                <span class="invalid-feedback">{{ $message }}</span>
                                            @enderror
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="engagement_rate">Taux d'engagement (%)</label>
                                            <input type="number" step="0.01" min="0" class="form-control @error('engagement_rate') is-invalid @enderror" 
                                                id="engagement_rate" name="engagement_rate" value="{{ old('engagement_rate', 0) }}">
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
                                <option value="pending_verification" {{ old('status') == 'pending_verification' ? 'selected' : '' }}>En attente de vérification</option>
                                <option value="verified" {{ old('status') == 'verified' ? 'selected' : '' }}>Vérifiée</option>
                            </select>
                            @error('status')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                    
                    <div class="card-footer">
                        <button type="submit" class="btn btn-primary">Ajouter</button>
                        <a href="{{ route('publications.index') }}" class="btn btn-secondary">Annuler</a>
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
        
        // Initialiser Select2 pour améliorer l'UX des listes déroulantes
        if (typeof $ !== 'undefined' && $.fn.select2) {
            // Configuration générale pour tous les select
            $.fn.select2.defaults.set('theme', 'bootstrap4');
            $.fn.select2.defaults.set('width', '100%');
            
            // Select pour les missions avec style amélioré
            $('#assignment_id').select2({
                placeholder: 'Sélectionner une mission',
                allowClear: true,
                templateResult: formatAssignment,
                templateSelection: formatAssignment
            });
            
            // Select pour les plateformes avec icônes
            $('#platform').select2({
                placeholder: 'Sélectionner une plateforme',
                allowClear: true,
                templateResult: formatPlatform,
                templateSelection: formatPlatform
            });
            
            // Select pour le type de publication
            $('#post_type').select2({
                placeholder: 'Sélectionner un type',
                allowClear: true,
                templateResult: formatPostType,
                templateSelection: formatPostType
            });
            
            // Select pour le statut
            $('#status').select2({
                placeholder: 'Sélectionner un statut',
                allowClear: true,
                templateResult: formatStatus,
                templateSelection: formatStatus
            });
            
            // Select pour le contenu principal
            $('#content_id').select2({
                placeholder: 'Sélectionner un contenu',
                allowClear: true
            });
            
            // Select pour les contenus multiples
            $('#contents').select2({
                placeholder: 'Sélectionner les contenus utilisés',
                allowClear: true
            });
            
            // Fonction pour formater les options de mission
            function formatAssignment(assignment) {
                if (!assignment.id) {
                    return assignment.text;
                }
                
                var $assignment = $(
                    '<span><i class="fas fa-tasks mr-2"></i> ' + assignment.text + '</span>'
                );
                return $assignment;
            };
            
            // Fonction pour formater les options de plateforme avec icônes
            function formatPlatform(platform) {
                if (!platform.id) {
                    return platform.text;
                }
                
                var icon = '';
                var color = '';
                
                switch(platform.id) {
                    case 'instagram':
                        icon = 'fa-instagram';
                        color = 'text-danger';
                        break;
                    case 'facebook':
                        icon = 'fa-facebook';
                        color = 'text-primary';
                        break;
                    case 'twitter':
                        icon = 'fa-twitter';
                        color = 'text-info';
                        break;
                    case 'tiktok':
                        icon = 'fa-tiktok';
                        color = 'text-dark';
                        break;
                    case 'youtube':
                        icon = 'fa-youtube';
                        color = 'text-danger';
                        break;
                    case 'linkedin':
                        icon = 'fa-linkedin';
                        color = 'text-primary';
                        break;
                    default:
                        icon = 'fa-globe';
                        color = 'text-secondary';
                }
                
                var $platform = $(
                    '<span><i class="fab ' + icon + ' mr-2 ' + color + '"></i> ' + platform.text + '</span>'
                );
                return $platform;
            };
            
            // Fonction pour formater les options de type de publication
            function formatPostType(type) {
                if (!type.id) {
                    return type.text;
                }
                
                var icon = '';
                
                switch(type.id) {
                    case 'post':
                        icon = 'fa-image';
                        break;
                    case 'story':
                        icon = 'fa-clock';
                        break;
                    case 'reel':
                        icon = 'fa-video';
                        break;
                    case 'video':
                        icon = 'fa-film';
                        break;
                    case 'short':
                        icon = 'fa-play-circle';
                        break;
                    case 'article':
                        icon = 'fa-newspaper';
                        break;
                    default:
                        icon = 'fa-file';
                }
                
                var $type = $(
                    '<span><i class="fas ' + icon + ' mr-2"></i> ' + type.text + '</span>'
                );
                return $type;
            };
            
            // Fonction pour formater les options de statut
            function formatStatus(status) {
                if (!status.id) {
                    return status.text;
                }
                
                var icon = '';
                var color = '';
                
                switch(status.id) {
                    case 'pending':
                        icon = 'fa-clock';
                        color = 'text-warning';
                        break;
                    case 'verified':
                        icon = 'fa-check-circle';
                        color = 'text-success';
                        break;
                    case 'rejected':
                        icon = 'fa-times-circle';
                        color = 'text-danger';
                        break;
                    default:
                        icon = 'fa-question-circle';
                        color = 'text-secondary';
                }
                
                var $status = $(
                    '<span><i class="fas ' + icon + ' mr-2 ' + color + '"></i> ' + status.text + '</span>'
                );
                return $status;
            };
            
            // Filtrer les contenus en fonction de la campagne sélectionnée
            $('#assignment_id').on('change', function() {
                var campaignId = $('option:selected', this).data('campaign-id');
                
                if (campaignId) {
                    // Filtrer le contenu principal
                    $('#content_id option').each(function() {
                        if ($(this).data('campaign-id') == campaignId) {
                            $(this).prop('disabled', false);
                        } else {
                            $(this).prop('disabled', true);
                        }
                    });
                    
                    // Filtrer les contenus multiples
                    $('#contents option').each(function() {
                        if ($(this).data('campaign-id') == campaignId) {
                            $(this).prop('disabled', false);
                        } else {
                            $(this).prop('disabled', true);
                        }
                    });
                } else {
                    $('#content_id option').prop('disabled', false);
                    $('#contents option').prop('disabled', false);
                }
                
                $('#content_id').val(null).trigger('change');
                $('#contents').val(null).trigger('change');
            });
            
            // Déclencher le changement initial pour filtrer les contenus
            $('#assignment_id').trigger('change');
        }
        
        // Auto-remplissage de la date de publication avec la date et heure actuelles
        if (!document.getElementById('published_at').value) {
            var now = new Date();
            var year = now.getFullYear();
            var month = (now.getMonth() + 1).toString().padStart(2, '0');
            var day = now.getDate().toString().padStart(2, '0');
            var hours = now.getHours().toString().padStart(2, '0');
            var minutes = now.getMinutes().toString().padStart(2, '0');
            
            document.getElementById('published_at').value = `${year}-${month}-${day}T${hours}:${minutes}`;
        }
    });
</script>
@endpush
