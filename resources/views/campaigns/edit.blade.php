@extends('layouts.adminlte')

@section('title', 'Modifier la campagne')

@section('content_header')
<div class="container-fluid">
    <div class="row mb-2">
        <div class="col-sm-6">
            <h1>Modifier la campagne</h1>
        </div>
        <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="{{ route('campaigns.index') }}">Campagnes</a></li>
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
                    <h3 class="card-title">Modification de "{{ $campaign->name }}"</h3>
                </div>
                
                <form method="POST" action="{{ route('campaigns.update', $campaign) }}">
                    @csrf
                    @method('PUT')
                    
                    <div class="card-body">
                        <div class="form-group">
                            <label for="name">Nom de la campagne <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('name') is-invalid @enderror" 
                                id="name" name="name" value="{{ old('name', $campaign->name) }}" required>
                            @error('name')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                        
                        <!-- Champ client supprimé car non requis par le contrôleur -->
                        
                        <div class="form-group">
                            <label for="description">Description</label>
                            <textarea class="form-control @error('description') is-invalid @enderror" 
                                id="description" name="description" rows="4">{{ old('description', $campaign->description) }}</textarea>
                            @error('description')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="start_date">Date de début</label>
                                    <input type="date" class="form-control @error('start_date') is-invalid @enderror" 
                                        id="start_date" name="start_date" 
                                        value="{{ old('start_date', $campaign->start_date ? $campaign->start_date->format('Y-m-d') : '') }}">
                                    @error('start_date')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="end_date">Date de fin</label>
                                    <input type="date" class="form-control @error('end_date') is-invalid @enderror" 
                                        id="end_date" name="end_date" 
                                        value="{{ old('end_date', $campaign->end_date ? $campaign->end_date->format('Y-m-d') : '') }}">
                                    @error('end_date')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label for="budget">Budget (FCFA)</label>
                            <input type="number" step="0.01" class="form-control @error('budget') is-invalid @enderror" 
                                id="budget" name="budget" value="{{ old('budget', $campaign->budget) }}">
                            @error('budget')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                        
                        <div class="form-group">
                            <label for="status">Statut <span class="text-danger">*</span></label>
                            <select class="form-control @error('status') is-invalid @enderror" id="status" name="status" required>
                                <option value="draft" {{ old('status', $campaign->status) == 'draft' ? 'selected' : '' }}>Brouillon</option>
                                <option value="active" {{ old('status', $campaign->status) == 'active' ? 'selected' : '' }}>Active</option>
                                <option value="paused" {{ old('status', $campaign->status) == 'paused' ? 'selected' : '' }}>En pause</option>
                                <option value="completed" {{ old('status', $campaign->status) == 'completed' ? 'selected' : '' }}>Terminée</option>
                            </select>
                            @error('status')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                        
                        <div class="form-group">
                            <label for="target_audience">Public cible</label>
                            <input type="text" class="form-control @error('target_audience') is-invalid @enderror" 
                                id="target_audience" name="target_audience" value="{{ old('target_audience', $campaign->target_audience) }}">
                            @error('target_audience')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                        
                        <div class="form-group">
                            <label for="objectives">Objectifs</label>
                            <div class="objectives-container">
                                <div class="objective-inputs">
                                    @if(old('objectives'))
                                        @foreach(old('objectives') as $index => $objective)
                                            <div class="input-group mb-2">
                                                <input type="text" class="form-control" name="objectives[]" value="{{ $objective }}" placeholder="Objectif {{ $index + 1 }}">
                                                <div class="input-group-append">
                                                    @if($index === 0)
                                                        <button type="button" class="btn btn-success add-objective"><i class="fas fa-plus"></i></button>
                                                    @else
                                                        <button type="button" class="btn btn-danger remove-objective"><i class="fas fa-minus"></i></button>
                                                    @endif
                                                </div>
                                            </div>
                                        @endforeach
                                    @elseif(is_array($campaign->objectives) && count($campaign->objectives) > 0)
                                        @foreach($campaign->objectives as $index => $objective)
                                            <div class="input-group mb-2">
                                                <input type="text" class="form-control" name="objectives[]" value="{{ $objective }}" placeholder="Objectif {{ $index + 1 }}">
                                                <div class="input-group-append">
                                                    @if($index === 0)
                                                        <button type="button" class="btn btn-success add-objective"><i class="fas fa-plus"></i></button>
                                                    @else
                                                        <button type="button" class="btn btn-danger remove-objective"><i class="fas fa-minus"></i></button>
                                                    @endif
                                                </div>
                                            </div>
                                        @endforeach
                                    @else
                                        <div class="input-group mb-2">
                                            <input type="text" class="form-control" name="objectives[]" placeholder="Objectif 1">
                                            <div class="input-group-append">
                                                <button type="button" class="btn btn-success add-objective"><i class="fas fa-plus"></i></button>
                                            </div>
                                        </div>
                                    @endif
                                </div>
                            </div>
                            <small class="form-text text-muted">Ajoutez autant d'objectifs que nécessaire</small>
                            @error('objectives')
                                <span class="invalid-feedback d-block">{{ $message }}</span>
                            @enderror
                        </div>
                        
                        @push('scripts')
                        <script>
                            $(document).ready(function() {
                                // Ajouter un nouvel objectif
                                $(document).on('click', '.add-objective', function() {
                                    var count = $('.objective-inputs .input-group').length + 1;
                                    var newInput = `
                                        <div class="input-group mb-2">
                                            <input type="text" class="form-control" name="objectives[]" placeholder="Objectif ${count}">
                                            <div class="input-group-append">
                                                <button type="button" class="btn btn-danger remove-objective"><i class="fas fa-minus"></i></button>
                                            </div>
                                        </div>
                                    `;
                                    $('.objective-inputs').append(newInput);
                                });
                                
                                // Supprimer un objectif
                                $(document).on('click', '.remove-objective', function() {
                                    $(this).closest('.input-group').remove();
                                    // Réindexer les placeholders
                                    $('.objective-inputs .input-group').each(function(index) {
                                        $(this).find('input').attr('placeholder', 'Objectif ' + (index + 1));
                                    });
                                });
                            });
                        </script>
                        @endpush
                        
                        <div class="form-group">
                            <label for="platforms">Plateformes <span class="text-danger">*</span></label>
                            <div class="row">
                                <div class="col-md-2">
                                    <div class="custom-control custom-checkbox">
                                        <input type="checkbox" class="custom-control-input" id="platform_facebook" name="platforms[]" value="facebook" {{ (is_array(old('platforms', json_decode($campaign->platforms, true))) && in_array('facebook', old('platforms', json_decode($campaign->platforms, true)))) ? 'checked' : '' }}>
                                        <label class="custom-control-label" for="platform_facebook">Facebook</label>
                                    </div>
                                </div>
                                <div class="col-md-2">
                                    <div class="custom-control custom-checkbox">
                                        <input type="checkbox" class="custom-control-input" id="platform_instagram" name="platforms[]" value="instagram" {{ (is_array(old('platforms', json_decode($campaign->platforms, true))) && in_array('instagram', old('platforms', json_decode($campaign->platforms, true)))) ? 'checked' : '' }}>
                                        <label class="custom-control-label" for="platform_instagram">Instagram</label>
                                    </div>
                                </div>
                                <div class="col-md-2">
                                    <div class="custom-control custom-checkbox">
                                        <input type="checkbox" class="custom-control-input" id="platform_twitter" name="platforms[]" value="twitter" {{ (is_array(old('platforms', json_decode($campaign->platforms, true))) && in_array('twitter', old('platforms', json_decode($campaign->platforms, true)))) ? 'checked' : '' }}>
                                        <label class="custom-control-label" for="platform_twitter">Twitter</label>
                                    </div>
                                </div>
                                <div class="col-md-2">
                                    <div class="custom-control custom-checkbox">
                                        <input type="checkbox" class="custom-control-input" id="platform_tiktok" name="platforms[]" value="tiktok" {{ (is_array(old('platforms', json_decode($campaign->platforms, true))) && in_array('tiktok', old('platforms', json_decode($campaign->platforms, true)))) ? 'checked' : '' }}>
                                        <label class="custom-control-label" for="platform_tiktok">TikTok</label>
                                    </div>
                                </div>
                                <div class="col-md-2">
                                    <div class="custom-control custom-checkbox">
                                        <input type="checkbox" class="custom-control-input" id="platform_youtube" name="platforms[]" value="youtube" {{ (is_array(old('platforms', json_decode($campaign->platforms, true))) && in_array('youtube', old('platforms', json_decode($campaign->platforms, true)))) ? 'checked' : '' }}>
                                        <label class="custom-control-label" for="platform_youtube">YouTube</label>
                                    </div>
                                </div>
                                <div class="col-md-2">
                                    <div class="custom-control custom-checkbox">
                                        <input type="checkbox" class="custom-control-input" id="platform_linkedin" name="platforms[]" value="linkedin" {{ (is_array(old('platforms', json_decode($campaign->platforms, true))) && in_array('linkedin', old('platforms', json_decode($campaign->platforms, true)))) ? 'checked' : '' }}>
                                        <label class="custom-control-label" for="platform_linkedin">LinkedIn</label>
                                    </div>
                                </div>
                            </div>
                            @error('platforms')
                                <span class="invalid-feedback d-block">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                    
                    @include('campaigns.partials.brand-charter-fields', ['campaign' => $campaign])

                    <div class="card-footer">
                        <button type="submit" class="btn btn-warning">Mettre à jour</button>
                        <a href="{{ route('campaigns.show', $campaign) }}" class="btn btn-secondary">Annuler</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
