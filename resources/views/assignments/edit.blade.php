@extends('layouts.adminlte')

@section('title', 'Modifier la mission')

@section('content_header')
<div class="container-fluid">
    <div class="row mb-2">
        <div class="col-sm-6">
            <h1>Modifier la mission</h1>
        </div>
        <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="{{ route('assignments.index') }}">Missions</a></li>
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
                    <h3 class="card-title">Modification de la mission #{{ $assignment->id }}</h3>
                </div>
                
                <form method="POST" action="{{ route('assignments.update', $assignment) }}">
                    @csrf
                    @method('PUT')
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="campaign_id">Campagne <span class="text-danger">*</span></label>
                                    <select class="form-control select2 @error('campaign_id') is-invalid @enderror" 
                                        id="campaign_id" name="campaign_id" required>
                                        <option value="">Sélectionner une campagne</option>
                                        @foreach($campaigns as $campaign)
                                            <option value="{{ $campaign->id }}" {{ old('campaign_id', $assignment->campaign_id) == $campaign->id ? 'selected' : '' }}>
                                                {{ $campaign->title }} {{ $campaign->description ? '- '.$campaign->description : '' }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('campaign_id')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                                
                                <div class="form-group">
                                    <label for="content_id">Contenu à publier <span class="text-danger">*</span></label>
                                    <select class="form-control select2 @error('content_id') is-invalid @enderror" 
                                        id="content_id" name="content_id" required>
                                        <option value="">Sélectionner d'abord une campagne</option>
                                        @if(!empty($contents))
                                            @foreach($contents as $content)
                                                <option value="{{ $content->id }}" {{ old('content_id', $assignment->content_id) == $content->id ? 'selected' : '' }}>
                                                    {{ $content->title }} {{ $content->type ? '(' . $content->type . ')' : '' }}
                                                </option>
                                            @endforeach
                                        @endif
                                    </select>
                                    @error('content_id')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                    <small class="form-text text-muted">Choisissez le contenu à publier par l'influenceur (obligatoire).</small>
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="influencer_id">Influenceur <span class="text-danger">*</span></label>
                                    <select class="form-control select2 @error('influencer_id') is-invalid @enderror" 
                                        id="influencer_id" name="influencer_id" required>
                                        <option value="">Sélectionner un influenceur</option>
                                        @foreach($influencers as $influencer)
                                            <option value="{{ $influencer->id }}" {{ old('influencer_id', $assignment->influencer_id) == $influencer->id ? 'selected' : '' }}>
                                                {{ $influencer->name }} - {{ $influencer->email }} {{ $influencer->profile ? '- '.$influencer->profile->specialty : '' }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('influencer_id')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="supervisor_id">Superviseur</label>
                                    <select class="form-control @error('supervisor_id') is-invalid @enderror" 
                                        id="supervisor_id" name="supervisor_id">
                                        <option value="">Sélectionner un superviseur</option>
                                        @foreach($supervisors as $supervisor)
                                            <option value="{{ $supervisor->id }}" {{ old('supervisor_id', $assignment->supervisor_id) == $supervisor->id ? 'selected' : '' }}>
                                                {{ $supervisor->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('supervisor_id')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="status">Statut <span class="text-danger">*</span></label>
                                    <select class="form-control @error('status') is-invalid @enderror" 
                                        id="status" name="status" required>
                                        <option value="pending" {{ old('status', $assignment->status) == 'pending' ? 'selected' : '' }}>En attente</option>
                                        <option value="accepted" {{ old('status', $assignment->status) == 'accepted' ? 'selected' : '' }}>Acceptée</option>
                                        <option value="in_progress" {{ old('status', $assignment->status) == 'in_progress' ? 'selected' : '' }}>En cours</option>
                                        <option value="completed" {{ old('status', $assignment->status) == 'completed' ? 'selected' : '' }}>Terminée</option>
                                    </select>
                                    @error('status')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="deadline">Date d'échéance</label>
                                    <input type="date" class="form-control @error('deadline') is-invalid @enderror" 
                                        id="deadline" name="deadline" value="{{ old('deadline', $assignment->deadline ? $assignment->deadline->format('Y-m-d') : '') }}">
                                    @error('deadline')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="compensation">Compensation (€)</label>
                                    <input type="number" step="0.01" class="form-control @error('compensation') is-invalid @enderror" 
                                        id="compensation" name="compensation" value="{{ old('compensation', $assignment->compensation) }}" placeholder="0.00">
                                    @error('compensation')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label for="description">Description de la mission</label>
                                    <textarea class="form-control @error('description') is-invalid @enderror" 
                                        id="description" name="description" rows="3">{{ old('description', $assignment->description) }}</textarea>
                                    @error('description')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label for="instructions">Instructions spécifiques</label>
                                    <textarea class="form-control @error('instructions') is-invalid @enderror" 
                                        id="instructions" name="instructions" rows="4">{{ old('instructions', $assignment->instructions) }}</textarea>
                                    <small class="form-text text-muted">
                                        Instructions détaillées pour l'influenceur (hashtags à utiliser, messages clés, etc.)
                                    </small>
                                    @error('instructions')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="required_posts">Publications requises</label>
                                    <input type="number" min="1" class="form-control @error('required_posts') is-invalid @enderror" 
                                        id="required_posts" name="required_posts" value="{{ old('required_posts', $assignment->required_posts) }}">
                                    <small class="form-text text-muted">
                                        Nombre de publications attendues de l'influenceur
                                    </small>
                                    @error('required_posts')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="platforms">Plateformes recommandées</label>
                                    <div class="select2-purple">
                                        <select class="form-control select2 @error('platforms') is-invalid @enderror" multiple 
                                            id="platforms" name="platforms[]">
                                            @php
                                                $currentPlatforms = old('platforms', $assignment->platforms ? json_decode($assignment->platforms) : []);
                                            @endphp
                                            <option value="instagram" {{ in_array('instagram', $currentPlatforms) ? 'selected' : '' }}>Instagram</option>
                                            <option value="facebook" {{ in_array('facebook', $currentPlatforms) ? 'selected' : '' }}>Facebook</option>
                                            <option value="twitter" {{ in_array('twitter', $currentPlatforms) ? 'selected' : '' }}>Twitter</option>
                                            <option value="tiktok" {{ in_array('tiktok', $currentPlatforms) ? 'selected' : '' }}>TikTok</option>
                                            <option value="youtube" {{ in_array('youtube', $currentPlatforms) ? 'selected' : '' }}>YouTube</option>
                                            <option value="linkedin" {{ in_array('linkedin', $currentPlatforms) ? 'selected' : '' }}>LinkedIn</option>
                                            <option value="blog" {{ in_array('blog', $currentPlatforms) ? 'selected' : '' }}>Blog</option>
                                            <option value="other" {{ in_array('other', $currentPlatforms) ? 'selected' : '' }}>Autre</option>
                                        </select>
                                        <small class="form-text text-muted">
                                            Sélectionnez une ou plusieurs plateformes (facultatif)
                                        </small>
                                    </div>
                                    @error('platforms')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        @if($assignment->status == 'completed')
                            <div class="alert alert-info">
                                <i class="icon fas fa-info-circle"></i> Cette mission est marquée comme terminée. La modification de certains champs peut nécessiter une mise à jour des publications associées.
                            </div>
                        @endif
                    </div>
                    
                    <div class="card-footer">
                        <button type="submit" class="btn btn-warning">Mettre à jour</button>
                        <a href="{{ route('assignments.show', $assignment) }}" class="btn btn-secondary">Annuler</a>
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
    // Force-reload des styles select2
    if ($.fn.select2) {
        // Configuration initiale des sélecteurs simple
        $('#campaign_id').select2({
            theme: 'bootstrap4',
            width: '100%',
            placeholder: 'Sélectionner une campagne',
            allowClear: true
        });
        
        $('#influencer_id').select2({
            theme: 'bootstrap4',
            width: '100%',
            placeholder: 'Sélectionner un influenceur',
            allowClear: true
        });
        
        $('#supervisor_id').select2({
            theme: 'bootstrap4',
            width: '100%',
            placeholder: 'Sélectionner un superviseur',
            allowClear: true
        });
        
        $('#content_id').select2({
            theme: 'bootstrap4',
            width: '100%',
            placeholder: 'Sélectionner un contenu',
            allowClear: true
        });
        
        // Configuration du sélecteur multiple
        $('#platforms').select2({
            theme: 'bootstrap4',
            width: '100%',
            placeholder: 'Sélectionner les plateformes',
            allowClear: true
        });
        
        // Charger les contenus en fonction de la campagne sélectionnée
        $('#campaign_id').on('change', function() {
            var selectedCampaignId = $(this).val();
            var contentSelect = $('#content_id');
            var savedContentId = "{{ $assignment->content_id ?: '' }}";
            
            if (!selectedCampaignId) {
                contentSelect.empty().append('<option value="">Sélectionner d\'abord une campagne</option>');
                contentSelect.val('').trigger('change');
                return;
            }
            
            // Ajouter une option de chargement temporaire
            contentSelect.empty().append('<option value="">Chargement des contenus...</option>');
            contentSelect.val('').trigger('change');
            
            // Récupérer les contenus via AJAX
            $.ajax({
                url: '/campaigns/' + selectedCampaignId + '/get-contents',
                type: 'GET',
                dataType: 'json',
                success: function(data) {
                    contentSelect.empty();
                    contentSelect.append('<option value="">Sélectionner un contenu</option>');
                    
                    if (data && data.length > 0) {
                        $.each(data, function(index, content) {
                            contentSelect.append('<option value="' + content.id + '">' + 
                                content.text + '</option>');
                        });
                        
                        // Restaurer la sélection précédente si elle existe
                        if (savedContentId) {
                            contentSelect.val(savedContentId);
                        }
                    } else {
                        contentSelect.append('<option value="">Aucun contenu disponible</option>');
                    }
                    
                    contentSelect.trigger('change');
                },
                error: function() {
                    contentSelect.empty();
                    contentSelect.append('<option value="">Erreur de chargement</option>');
                    contentSelect.trigger('change');
                }
            });
        });
        
        // Pré-sélectionner les valeurs
        var campaignId = "{{ $assignment->campaign_id }}";
        var influencerId = "{{ $assignment->influencer_id }}";
        var supervisorId = "{{ $assignment->supervisor_id ?: '' }}";
        var platformsJson = '{{ $assignment->platforms ?: "[]" }}';
        
        if (campaignId) {
            $('#campaign_id').val(campaignId).trigger('change');
        }
        
        if (influencerId) {
            $('#influencer_id').val(influencerId).trigger('change');
        }
        
        if (supervisorId) {
            $('#supervisor_id').val(supervisorId).trigger('change');
        }
        
        // Définir les plateformes sélectionnées
        try {
            var platforms = JSON.parse(platformsJson.replace(/&quot;/g, '"'));
            if (Array.isArray(platforms) && platforms.length > 0) {
                $('#platforms').val(platforms).trigger('change');
            }
        } catch (e) {
            console.error('Erreur de parsing des plateformes:', e);
        }
    }
});
</script>
<style>
    /* Amélioration du style des sélecteurs */
    .select2-container--bootstrap4 .select2-selection {
        border: 1px solid #ced4da;
        border-radius: 0.25rem;
    }
    
    .select2-container--bootstrap4.select2-container--focus .select2-selection {
        border-color: #ffc107;
        box-shadow: 0 0 0 0.2rem rgba(255, 193, 7, 0.25);
    }
    
    .select2-container--bootstrap4 .select2-results__option--highlighted[aria-selected] {
        background-color: #ffc107;
        color: #000;
    }
    
    .select2-container--bootstrap4 .select2-selection--multiple .select2-selection__choice {
        background-color: #ffc107;
        border-color: #e0a800;
        color: #000;
    }
    
    .select2-container--bootstrap4 .select2-selection--multiple .select2-selection__choice__remove {
        color: #000;
        margin-right: 5px;
    }
    
    .select2-container--bootstrap4 .select2-selection--multiple .select2-selection__choice__remove:hover {
        color: #000;
        background-color: #e0a800;
    }
</style>
@endpush
