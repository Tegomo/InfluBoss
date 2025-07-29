@extends('layouts.adminlte')

@section('title', 'Détails de la campagne')

@section('content_header')
<div class="container-fluid">
    <div class="row mb-2">
        <div class="col-sm-6">
            <h1>{{ $campaign->name }}</h1>
        </div>
        <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="{{ route('campaigns.index') }}">Campagnes</a></li>
                <li class="breadcrumb-item active">{{ $campaign->name }}</li>
            </ol>
        </div>
    </div>
</div>
@endsection

@section('content')
<div class="container-fluid">
    <div class="row">
        <!-- Informations de la campagne -->
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Informations générales</h3>
                    <div class="card-tools">
                        <a href="{{ route('campaigns.edit', $campaign) }}" class="btn btn-sm btn-warning">
                            <i class="fas fa-edit"></i> Modifier
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4 text-bold">Plateformes:</div>
                        <div class="col-md-8">
                            @if(is_array($campaign->platforms))
                                @foreach($campaign->platforms as $platform)
                                    <span class="badge badge-primary">{{ $platform }}</span>
                                @endforeach
                            @else
                                Non spécifiées
                            @endif
                        </div>
                    </div>
                    <hr>
                    <div class="row">
                        <div class="col-md-4 text-bold">Statut:</div>
                        <div class="col-md-8">
                            @switch($campaign->status)
                                @case('draft')
                                    <span class="badge badge-secondary">Brouillon</span>
                                    @break
                                @case('active')
                                    <span class="badge badge-success">Active</span>
                                    @break
                                @case('paused')
                                    <span class="badge badge-warning">En pause</span>
                                    @break
                                @case('completed')
                                    <span class="badge badge-info">Terminée</span>
                                    @break
                                @default
                                    <span class="badge badge-secondary">{{ $campaign->status }}</span>
                            @endswitch
                        </div>
                    </div>
                    <hr>
                    <div class="row">
                        <div class="col-md-4 text-bold">Date de début:</div>
                        <div class="col-md-8">{{ $campaign->start_date ? $campaign->start_date->format('d/m/Y') : 'Non définie' }}</div>
                    </div>
                    <hr>
                    <div class="row">
                        <div class="col-md-4 text-bold">Date de fin:</div>
                        <div class="col-md-8">{{ $campaign->end_date ? $campaign->end_date->format('d/m/Y') : 'Non définie' }}</div>
                    </div>
                    <hr>
                    <div class="row">
                        <div class="col-md-4 text-bold">Budget:</div>
                        <div class="col-md-8">{{ $campaign->budget ? number_format($campaign->budget, 2, ',', ' ') . ' FCFA' : 'Non défini' }}</div>
                    </div>
                    <hr>
                    <div class="row">
                        <div class="col-md-4 text-bold">Influenceurs:</div>
                        <div class="col-md-8">
                            @php
                                $influencers = $campaign->assignments()->with('influencer')->get()->pluck('influencer')->unique('id');
                            @endphp
                            
                            @if($influencers->count() > 0)
                                <ul class="list-unstyled">
                                    @foreach($influencers as $influencer)
                                        @if($influencer)
                                            <li>
                                                <a href="{{ route('users.show', $influencer) }}">
                                                    {{ $influencer->name }}
                                                </a>
                                            </li>
                                        @endif
                                    @endforeach
                                </ul>
                            @else
                                <p>Aucun influenceur assigné</p>
                            @endif
                        </div>
                    </div>
                    <hr>
                    <div class="row">
                        <div class="col-md-4 text-bold">Superviseurs:</div>
                        <div class="col-md-8">
                            @php
                                $supervisors = $campaign->assignments()->with('supervisor')->get()->pluck('supervisor')->unique('id');
                            @endphp
                            
                            @if($supervisors->count() > 0)
                                <ul class="list-unstyled">
                                    @foreach($supervisors as $supervisor)
                                        @if($supervisor)
                                            <li>
                                                <a href="{{ route('users.show', $supervisor) }}">
                                                    {{ $supervisor->name }}
                                                </a>
                                            </li>
                                        @endif
                                    @endforeach
                                </ul>
                            @else
                                <p>Aucun superviseur assigné</p>
                            @endif
                        </div>
                    </div>
                    <hr>
                    <div class="row">
                        <div class="col-md-4 text-bold">Créée par:</div>
                        <div class="col-md-8">{{ $campaign->creator ? $campaign->creator->name : 'Utilisateur inconnu' }}</div>
                    </div>
                    <hr>
                    <div class="row">
                        <div class="col-md-4 text-bold">Créée le:</div>
                        <div class="col-md-8">{{ $campaign->created_at->format('d/m/Y H:i') }}</div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Description et objectifs -->
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Description et objectifs</h3>
                </div>
                <div class="card-body">
                    <div class="mb-4">
                        <h5>Description</h5>
                        <p>{{ $campaign->description ?: 'Aucune description fournie.' }}</p>
                    </div>
                    <hr>
                    <div>
                        <h5>Objectifs</h5>
                        <p>{{ $campaign->objectives ?: 'Aucun objectif défini.' }}</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Contenus associés -->
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Contenus de la campagne</h3>
                    <div class="card-tools">
                        <a href="{{ route('contents.create', ['campaign_id' => $campaign->id]) }}" class="btn btn-sm btn-primary">
                            <i class="fas fa-plus"></i> Ajouter un contenu
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <table class="table table-bordered table-striped">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Titre</th>
                                <th>Type</th>
                                <th>Date de création</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($campaign->contents ?? [] as $content)
                            <tr>
                                <td>{{ $content->id }}</td>
                                <td>{{ $content->title }}</td>
                                <td>{{ $content->content_type }}</td>
                                <td>{{ $content->created_at->format('d/m/Y') }}</td>
                                <td>
                                    <div class="btn-group">
                                        <a href="{{ route('contents.show', $content) }}" class="btn btn-sm btn-info">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <a href="{{ route('contents.download', $content) }}" class="btn btn-sm btn-success">
                                            <i class="fas fa-download"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="5" class="text-center">Aucun contenu associé à cette campagne</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Missions associées -->
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Missions associées</h3>
                </div>
                <div class="card-body">
                    <table class="table table-bordered table-striped">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Influenceur</th>
                                <th>Superviseur</th>
                                <th>Statut</th>
                                <th>Date d'assignation</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($campaign->assignments ?? [] as $assignment)
                            <tr>
                                <td>{{ $assignment->id }}</td>
                                <td>{{ $assignment->influencer->name ?? 'N/A' }}</td>
                                <td>{{ $assignment->supervisor->name ?? 'N/A' }}</td>
                                <td>
                                    @switch($assignment->status)
                                        @case('pending')
                                            <span class="badge badge-warning">En attente</span>
                                            @break
                                        @case('accepted')
                                            <span class="badge badge-info">Acceptée</span>
                                            @break
                                        @case('in_progress')
                                            <span class="badge badge-primary">En cours</span>
                                            @break
                                        @case('completed')
                                            <span class="badge badge-success">Terminée</span>
                                            @break
                                        @default
                                            <span class="badge badge-secondary">{{ $assignment->status }}</span>
                                    @endswitch
                                </td>
                                <td>{{ $assignment->created_at->format('d/m/Y') }}</td>
                                <td>
                                    <a href="{{ route('assignments.show', $assignment) }}" class="btn btn-sm btn-info">
                                        <i class="fas fa-eye"></i> Détails
                                    </a>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="6" class="text-center">Aucune mission associée à cette campagne</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
