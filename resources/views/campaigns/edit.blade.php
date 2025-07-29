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
                    <h3 class="card-title">Modification de "{{ $campaign->title }}"</h3>
                </div>
                
                <form method="POST" action="{{ route('campaigns.update', $campaign) }}">
                    @csrf
                    @method('PUT')
                    
                    <div class="card-body">
                        <div class="form-group">
                            <label for="title">Titre <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('title') is-invalid @enderror" 
                                id="title" name="title" value="{{ old('title', $campaign->title) }}" required>
                            @error('title')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                        
                        <div class="form-group">
                            <label for="client">Client <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('client') is-invalid @enderror" 
                                id="client" name="client" value="{{ old('client', $campaign->client) }}" required>
                            @error('client')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                        
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
                            <label for="budget">Budget (€)</label>
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
                            <label for="objectives">Objectifs</label>
                            <textarea class="form-control @error('objectives') is-invalid @enderror" 
                                id="objectives" name="objectives" rows="3">{{ old('objectives', $campaign->objectives) }}</textarea>
                            @error('objectives')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                    
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
