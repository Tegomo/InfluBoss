@extends('layouts.adminlte')

@section('title', 'Notes pour la mission #' . $assignment->id)

@section('content_header')
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1>Notes pour la mission #{{ $assignment->id }}</h1>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Tableau de bord</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('assignments.index') }}">Missions</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('assignments.show', $assignment) }}">Mission #{{ $assignment->id }}</a></li>
                    <li class="breadcrumb-item active">Notes</li>
                </ol>
            </div>
        </div>
    </div>
@stop

@section('content')
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Liste des notes</h3>
                        <div class="card-tools">
                            <a href="{{ route('notes.create', $assignment) }}" class="btn btn-primary btn-sm">
                                <i class="fas fa-plus"></i> Nouvelle note
                            </a>
                        </div>
                    </div>
                    <div class="card-body">
                        @if($notes->isEmpty())
                            <div class="alert alert-info">
                                Aucune note n'a été ajoutée pour cette mission.
                            </div>
                        @else
                            <div class="timeline">
                                @foreach($notes as $note)
                                    <div class="time-label">
                                        <span class="bg-info">{{ $note->created_at->format('d/m/Y') }}</span>
                                    </div>
                                    <div>
                                        <i class="fas fa-comment bg-primary"></i>
                                        <div class="timeline-item">
                                            <span class="time"><i class="fas fa-clock"></i> {{ $note->created_at->format('H:i') }}</span>
                                            <h3 class="timeline-header">
                                                <strong>{{ $note->user->name }}</strong> a ajouté une note
                                            </h3>
                                            <div class="timeline-body">
                                                {{ $note->content }}
                                            </div>
                                            <div class="timeline-footer">
                                                <form action="{{ route('notes.destroy', $note) }}" method="POST" class="d-inline">
                                                    @csrf
                                                    @method('DELETE')
                                                    <a href="{{ route('notes.edit', $note) }}" class="btn btn-primary btn-sm">
                                                        <i class="fas fa-pencil-alt"></i> Modifier
                                                    </a>
                                                    <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Êtes-vous sûr de vouloir supprimer cette note ?')">
                                                        <i class="fas fa-trash"></i> Supprimer
                                                    </button>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                            <div class="d-flex justify-content-center mt-4">
                                {{ $notes->links() }}
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
@stop
