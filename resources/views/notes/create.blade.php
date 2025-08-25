@extends('layouts.adminlte')

@section('title', 'Ajouter une note à la mission #' . $assignment->id)

@section('content_header')
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1>Ajouter une note</h1>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Tableau de bord</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('assignments.index') }}">Missions</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('assignments.show', $assignment) }}">Mission #{{ $assignment->id }}</a></li>
                    <li class="breadcrumb-item active">Nouvelle note</li>
                </ol>
            </div>
        </div>
    </div>
@stop

@section('content')
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-12">
                <div class="card card-primary">
                    <div class="card-header">
                        <h3 class="card-title">Informations de la note</h3>
                    </div>
                    <form method="POST" action="{{ route('notes.store', $assignment) }}">
                        @csrf
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="form-group">
                                        <label for="content">Contenu de la note <span class="text-danger">*</span></label>
                                        <textarea 
                                            name="content" 
                                            id="content" 
                                            class="form-control @error('content') is-invalid @enderror" 
                                            rows="5"
                                            placeholder="Saisissez le contenu de votre note..."
                                            required
                                        >{{ old('content') }}</textarea>
                                        @error('content')
                                            <span class="invalid-feedback" role="alert">
                                                <strong>{{ $message }}</strong>
                                            </span>
                                        @enderror
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="card-footer">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save mr-1"></i> Enregistrer
                            </button>
                            <a href="{{ route('assignments.show', $assignment) }}" class="btn btn-secondary">
                                <i class="fas fa-times mr-1"></i> Annuler
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@stop
