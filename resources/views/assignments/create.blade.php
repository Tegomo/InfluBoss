@extends('layouts.adminlte')

@section('title', 'Créer une mission')

@section('content_header')
<div class="container-fluid">
    <div class="row mb-2">
        <div class="col-sm-6">
            <h1>Créer une mission</h1>
        </div>
        <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="{{ route('assignments.index') }}">Missions</a></li>
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
                    <h3 class="card-title">Informations de la mission</h3>
                </div>
                
                <form method="POST" action="{{ route('assignments.store') }}">
                    @csrf
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="campaign_id">Campagne <span class="text-danger">*</span></label>
                                    <select class="form-control select2 @error('campaign_id') is-invalid @enderror" 
                                        id="campaign_id" name="campaign_id" required>
                                        <option value="">Sélectionner une campagne</option>
                                        @foreach($campaigns as $campaign)
                                            <option value="{{ $campaign->id }}" {{ old('campaign_id') == $campaign->id || request('campaign_id') == $campaign->id ? 'selected' : '' }}>
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
                                                <option value="{{ $content->id }}" {{ old('content_id') == $content->id ? 'selected' : '' }}>
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
                                    <label>Influenceur(s) <span class="text-danger">*</span></label>
                                    <div class="form-group">
                                        <div class="input-group mb-2">
                                            <div class="input-group-prepend">
                                                <span class="input-group-text"><i class="fas fa-search"></i></span>
                                            </div>
                                            <input type="text" class="form-control" id="influencer-search" placeholder="Rechercher un influenceur...">
                                        </div>
                                        <div class="card card-body bg-light overflow-auto" style="max-height: 250px;" id="influencers-container">
                                            @foreach($influencers as $influencer)
                                                <div class="custom-control custom-checkbox influencer-item">
                                                    <input type="checkbox" class="custom-control-input" 
                                                        id="influencer_{{ $influencer->id }}" 
                                                        name="influencer_ids[]" 
                                                        value="{{ $influencer->id }}" 
                                                        {{ (is_array(old('influencer_ids')) && in_array($influencer->id, old('influencer_ids'))) ? 'checked' : '' }}>
                                                    <label class="custom-control-label" for="influencer_{{ $influencer->id }}">
                                                        {{ $influencer->name }} - {{ $influencer->email }} {{ $influencer->profile ? '- '.$influencer->profile->specialty : '' }}
                                                    </label>
                                                </div>
                                            @endforeach
                                        </div>
                                        <div class="mt-2">
                                            <button type="button" class="btn btn-sm btn-outline-primary" id="select-all-influencers">Sélectionner tout</button>
                                            <button type="button" class="btn btn-sm btn-outline-secondary" id="deselect-all-influencers">Désélectionner tout</button>
                                        </div>
                                    </div>
                                    <small class="form-text text-muted">Vous pouvez sélectionner plusieurs influenceurs pour créer plusieurs missions en une seule fois.</small>
                                    @error('influencer_ids')
                                        <span class="invalid-feedback d-block">{{ $message }}</span>
                                    @enderror
                                    @error('influencer_ids.*')
                                        <span class="invalid-feedback d-block">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="supervisor_id">Superviseur</label>
                                    <select class="form-control select2 @error('supervisor_id') is-invalid @enderror" 
                                        id="supervisor_id" name="supervisor_id" {{ Auth::user()->role === 'supervisor' ? 'disabled' : '' }}>
                                        @if(Auth::user()->role !== 'supervisor')
                                            <option value="">Sélectionner un superviseur</option>
                                            @foreach($supervisors as $supervisor)
                                                <option value="{{ $supervisor->id }}" {{ old('supervisor_id') == $supervisor->id ? 'selected' : '' }}>
                                                    {{ $supervisor->name }}
                                                </option>
                                            @endforeach
                                        @else
                                            <option value="{{ Auth::id() }}" selected>{{ Auth::user()->name }} (Vous)</option>
                                        @endif
                                    </select>
                                    @if(Auth::user()->role === 'supervisor')
                                        <input type="hidden" name="supervisor_id" value="{{ Auth::id() }}">
                                    @endif
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
                                        <option value="pending" {{ old('status') == 'pending' ? 'selected' : '' }}>En attente</option>
                                        <option value="accepted" {{ old('status') == 'accepted' ? 'selected' : '' }}>Acceptée</option>
                                        <option value="in_progress" {{ old('status') == 'in_progress' ? 'selected' : '' }}>En cours</option>
                                        <option value="completed" {{ old('status') == 'completed' ? 'selected' : '' }}>Terminée</option>
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
                                        id="deadline" name="deadline" value="{{ old('deadline') }}">
                                    @error('deadline')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="compensation">Compensation (FCFA)</label>
                                    <input type="number" step="0.01" class="form-control @error('compensation') is-invalid @enderror" 
                                        id="compensation" name="compensation" value="{{ old('compensation') }}" placeholder="0.00">
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
                                        id="description" name="description" rows="3">{{ old('description') }}</textarea>
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
                                        id="instructions" name="instructions" rows="4">{{ old('instructions') }}</textarea>
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
                                        id="required_posts" name="required_posts" value="{{ old('required_posts', 1) }}">
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
                                            <option value="instagram" {{ in_array('instagram', old('platforms', [])) ? 'selected' : '' }}>Instagram</option>
                                            <option value="facebook" {{ in_array('facebook', old('platforms', [])) ? 'selected' : '' }}>Facebook</option>
                                            <option value="twitter" {{ in_array('twitter', old('platforms', [])) ? 'selected' : '' }}>Twitter</option>
                                            <option value="tiktok" {{ in_array('tiktok', old('platforms', [])) ? 'selected' : '' }}>TikTok</option>
                                            <option value="youtube" {{ in_array('youtube', old('platforms', [])) ? 'selected' : '' }}>YouTube</option>
                                            <option value="linkedin" {{ in_array('linkedin', old('platforms', [])) ? 'selected' : '' }}>LinkedIn</option>
                                            <option value="blog" {{ in_array('blog', old('platforms', [])) ? 'selected' : '' }}>Blog</option>
                                            <option value="other" {{ in_array('other', old('platforms', [])) ? 'selected' : '' }}>Autre</option>
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
                    </div>
                    
                    <div class="card-footer">
                        <button type="submit" class="btn btn-primary">Créer</button>
                        <a href="{{ route('assignments.index') }}" class="btn btn-secondary">Annuler</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
$(function () {
    // Pour les autres éléments Select2 qui pourraient exister
    $('.select2').select2();
    
    // Fonctionnalité de recherche d'influenceurs
    $('#influencer-search').on('keyup', function() {
        const searchText = $(this).val().toLowerCase();
        
        $('.influencer-item').each(function() {
            const itemText = $(this).text().toLowerCase();
            if (itemText.includes(searchText)) {
                $(this).show();
            } else {
                $(this).hide();
            }
        });
    });
    
    // Sélectionner tous les influenceurs
    $('#select-all-influencers').on('click', function() {
        $('.influencer-item:visible input[type="checkbox"]').prop('checked', true);
    });
    
    // Désélectionner tous les influenceurs
    $('#deselect-all-influencers').on('click', function() {
        $('.influencer-item:visible input[type="checkbox"]').prop('checked', false);
    });
    
    // Charger les contenus en fonction de la campagne sélectionnée
    $('#campaign_id').on('change', function() {
        const campaignId = $(this).val();
        const contentSelect = $('#content_id');
        
        // Réinitialiser le select des contenus
        contentSelect.empty().append('<option value="">Chargement des contenus...</option>');
        
        if (campaignId) {
            // Faire une requête AJAX pour récupérer les contenus de la campagne
            $.ajax({
                url: '/campaigns/' + campaignId + '/get-contents',
                type: 'GET',
                dataType: 'json',
                success: function(data) {
                    contentSelect.empty();
                    
                    if (data.length === 0) {
                        contentSelect.append('<option value="">Aucun contenu disponible</option>');
                    } else {
                        contentSelect.append('<option value="">Sélectionner un contenu</option>');
                        
                        $.each(data, function(index, content) {
                            contentSelect.append('<option value="' + content.id + '">' + content.text + '</option>');
                        });
                    }
                    
                    // Rafraîchir Select2
                    contentSelect.trigger('change');
                },
                error: function() {
                    contentSelect.empty().append('<option value="">Erreur lors du chargement</option>');
                }
            });
        } else {
            contentSelect.empty().append('<option value="">Sélectionner d\'abord une campagne</option>');
            contentSelect.trigger('change');
        }
    });
    
    // Vérifier que au moins un influenceur est sélectionné à la soumission
    $('form').on('submit', function(e) {
        if ($('input[name="influencer_ids[]"]:checked').length === 0) {
            e.preventDefault();
            alert('Veuillez sélectionner au moins un influenceur pour la mission.');
            $('#influencers-container').addClass('border border-danger');
            setTimeout(function() {
                $('#influencers-container').removeClass('border border-danger');
            }, 3000);
        }
    });
});
</script>
<style>
    /* Styles personnalisés pour les sélections multiples */
    .select2-container--bootstrap4 .select2-selection--multiple .select2-selection__choice {
        background-color: #fd7e14;
        border-color: #fd7e14;
        color: #fff;
    }
    .select2-container--bootstrap4 .select2-selection--multiple .select2-selection__choice__remove {
        color: #fff;
    }
    .select2-container--bootstrap4 .select2-selection--multiple .select2-selection__choice__remove:hover {
        color: #fff;
        background-color: #e67211;
    }
</style>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Si disponible, initialiser Select2 pour améliorer l'UX des listes déroulantes
        if (typeof $ !== 'undefined' && $.fn.select2) {
            $('#campaign_id').select2({
                placeholder: 'Sélectionner une campagne',
                allowClear: true
            });
            
            $('#influencer_ids').select2({
                placeholder: 'Sélectionner un ou plusieurs influenceurs',
                allowClear: true,
                multiple: true
            });
            
            $('#supervisor_id').select2({
                placeholder: 'Sélectionner un superviseur',
                allowClear: true
            });
            
            $('#platforms').select2({
                placeholder: 'Sélectionner une ou plusieurs plateformes',
                allowClear: true,
                multiple: true
            });
        }
    });
</script>
@endpush
