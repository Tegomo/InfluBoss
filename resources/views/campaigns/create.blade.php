@extends('layouts.adminlte')

@section('title', 'Créer une campagne')

@section('content_header')
<div class="container-fluid">
    <div class="row mb-2">
        <div class="col-sm-6">
            <h1>Créer une campagne</h1>
        </div>
        <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="{{ route('campaigns.index') }}">Campagnes</a></li>
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
                    <h3 class="card-title">Informations de la campagne</h3>
                </div>
                
                <form method="POST" action="{{ route('campaigns.store') }}">
                    @csrf
                    <div class="card-body">
                        <div class="form-group">
                            <label for="name">Nom de la campagne <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('name') is-invalid @enderror" 
                                id="name" name="name" value="{{ old('name') }}" required>
                            @error('name')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                        
                        <!-- Champ client supprimé car non requis par le contrôleur -->
                        
                        <div class="form-group">
                            <label for="description">Description</label>
                            <textarea class="form-control @error('description') is-invalid @enderror" 
                                id="description" name="description" rows="4">{{ old('description') }}</textarea>
                            @error('description')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="start_date">Date de début</label>
                                    <input type="date" class="form-control @error('start_date') is-invalid @enderror" 
                                        id="start_date" name="start_date" value="{{ old('start_date') }}">
                                    @error('start_date')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="end_date">Date de fin</label>
                                    <input type="date" class="form-control @error('end_date') is-invalid @enderror" 
                                        id="end_date" name="end_date" value="{{ old('end_date') }}">
                                    @error('end_date')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label for="budget">Budget (FCFA)</label>
                            <input type="number" step="0.01" class="form-control @error('budget') is-invalid @enderror" 
                                id="budget" name="budget" value="{{ old('budget') }}">
                            @error('budget')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                        
                        <div class="form-group">
                            <label for="status">Statut <span class="text-danger">*</span></label>
                            <select class="form-control @error('status') is-invalid @enderror" id="status" name="status" required>
                                <option value="draft" {{ old('status') == 'draft' ? 'selected' : '' }}>Brouillon</option>
                                <option value="active" {{ old('status') == 'active' ? 'selected' : '' }}>Active</option>
                                <option value="paused" {{ old('status') == 'paused' ? 'selected' : '' }}>En pause</option>
                                <option value="completed" {{ old('status') == 'completed' ? 'selected' : '' }}>Terminée</option>
                            </select>
                            @error('status')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                        
                        <div class="form-group">
                            <label for="objectives">Objectifs</label>
                            <textarea class="form-control @error('objectives') is-invalid @enderror" 
                                id="objectives" name="objectives" rows="3">{{ old('objectives') }}</textarea>
                            <small class="form-text text-muted">Séparez les objectifs par une virgule</small>
                            @error('objectives')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                        
                        <div class="form-group">
                            <label for="platforms">Plateformes <span class="text-danger">*</span></label>
                            <div class="row">
                                <div class="col-md-2">
                                    <div class="custom-control custom-checkbox">
                                        <input type="checkbox" class="custom-control-input" id="platform_facebook" name="platforms[]" value="facebook" {{ (is_array(old('platforms')) && in_array('facebook', old('platforms'))) ? 'checked' : '' }}>
                                        <label class="custom-control-label" for="platform_facebook">Facebook</label>
                                    </div>
                                </div>
                                <div class="col-md-2">
                                    <div class="custom-control custom-checkbox">
                                        <input type="checkbox" class="custom-control-input" id="platform_instagram" name="platforms[]" value="instagram" {{ (is_array(old('platforms')) && in_array('instagram', old('platforms'))) ? 'checked' : '' }}>
                                        <label class="custom-control-label" for="platform_instagram">Instagram</label>
                                    </div>
                                </div>
                                <div class="col-md-2">
                                    <div class="custom-control custom-checkbox">
                                        <input type="checkbox" class="custom-control-input" id="platform_twitter" name="platforms[]" value="twitter" {{ (is_array(old('platforms')) && in_array('twitter', old('platforms'))) ? 'checked' : '' }}>
                                        <label class="custom-control-label" for="platform_twitter">Twitter</label>
                                    </div>
                                </div>
                                <div class="col-md-2">
                                    <div class="custom-control custom-checkbox">
                                        <input type="checkbox" class="custom-control-input" id="platform_tiktok" name="platforms[]" value="tiktok" {{ (is_array(old('platforms')) && in_array('tiktok', old('platforms'))) ? 'checked' : '' }}>
                                        <label class="custom-control-label" for="platform_tiktok">TikTok</label>
                                    </div>
                                </div>
                                <div class="col-md-2">
                                    <div class="custom-control custom-checkbox">
                                        <input type="checkbox" class="custom-control-input" id="platform_youtube" name="platforms[]" value="youtube" {{ (is_array(old('platforms')) && in_array('youtube', old('platforms'))) ? 'checked' : '' }}>
                                        <label class="custom-control-label" for="platform_youtube">YouTube</label>
                                    </div>
                                </div>
                                <div class="col-md-2">
                                    <div class="custom-control custom-checkbox">
                                        <input type="checkbox" class="custom-control-input" id="platform_linkedin" name="platforms[]" value="linkedin" {{ (is_array(old('platforms')) && in_array('linkedin', old('platforms'))) ? 'checked' : '' }}>
                                        <label class="custom-control-label" for="platform_linkedin">LinkedIn</label>
                                    </div>
                                </div>
                            </div>
                            @error('platforms')
                                <span class="invalid-feedback d-block">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                    
                    <div class="card-footer">
                        <button type="submit" class="btn btn-primary">Créer</button>
                        <a href="{{ route('campaigns.index') }}" class="btn btn-secondary">Annuler</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
