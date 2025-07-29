@extends('layouts.adminlte')

@section('title', 'Gestion des missions')

@section('content_header')
<div class="container-fluid">
    <div class="row mb-2">
        <div class="col-sm-6">
            <h1>Missions</h1>
        </div>
        <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                <li class="breadcrumb-item active">Missions</li>
            </ol>
        </div>
    </div>
</div>
@endsection

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Liste des missions</h3>
                    <div class="card-tools">
                        @if(Auth::user()->role === 'admin' || Auth::user()->role === 'super_admin' || Auth::user()->role === 'supervisor')
                        <a href="{{ route('assignments.create') }}" class="btn btn-primary btn-sm">
                            <i class="fas fa-plus"></i> Nouvelle mission
                        </a>
                        @endif
                    </div>
                </div>
                <div class="card-body">
                    <!-- Filtres -->
                    <form action="{{ route('assignments.index') }}" method="GET" class="mb-4">
                        <div class="row">
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="campaign_id">Campagne</label>
                                    <select class="form-control" id="campaign_id" name="campaign_id">
                                        <option value="">Toutes les campagnes</option>
                                        @foreach($campaigns as $campaign)
                                            <option value="{{ $campaign->id }}" {{ request('campaign_id') == $campaign->id ? 'selected' : '' }}>
                                                {{ $campaign->title }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="status">Statut</label>
                                    <select class="form-control" id="status" name="status">
                                        <option value="">Tous les statuts</option>
                                        <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>En attente</option>
                                        <option value="accepted" {{ request('status') == 'accepted' ? 'selected' : '' }}>Acceptée</option>
                                        <option value="in_progress" {{ request('status') == 'in_progress' ? 'selected' : '' }}>En cours</option>
                                        <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>Terminée</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="search">Recherche</label>
                                    <input type="text" class="form-control" id="search" name="search" 
                                        placeholder="Rechercher un influenceur..." value="{{ request('search') }}">
                                </div>
                            </div>
                            <div class="col-md-2 d-flex align-items-end">
                                <button type="submit" class="btn btn-primary mr-2">Filtrer</button>
                                <a href="{{ route('assignments.index') }}" class="btn btn-secondary">Réinitialiser</a>
                            </div>
                        </div>
                    </form>

                    <!-- Tableau des missions -->
                    <table class="table table-bordered table-striped">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Influenceur</th>
                                <th>Campagne</th>
                                @if(Auth::user()->role !== 'supervisor')
                                <th>Superviseur</th>
                                @endif
                                <th>Statut</th>
                                <th>Date d'assignation</th>
                                <th>Publications</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($assignments as $assignment)
                            <tr>
                                <td>{{ $assignment->id }}</td>
                                <td>
                                    @if($assignment->influencer)
                                        <div class="d-flex align-items-center">
                                            <div class="mr-2">
                                                <i class="fas fa-user-circle fa-lg text-orange"></i>
                                            </div>
                                            <div>
                                                {{ $assignment->influencer->name }}
                                                <br>
                                                <small class="text-muted">{{ $assignment->influencer->email }}</small>
                                            </div>
                                        </div>
                                    @else
                                        N/A
                                    @endif
                                </td>
                                <td>
                                    @if($assignment->campaign)
                                        {{ $assignment->campaign->name ?? 'N/A' }}
                                    @else
                                        N/A
                                    @endif
                                </td>
                                @if(Auth::user()->role !== 'supervisor')
                                <td>{{ $assignment->supervisor->name ?? 'N/A' }}</td>
                                @endif
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
                                <td class="text-center">
                                    <span class="badge bg-orange">{{ $assignment->publications_count }}</span>
                                </td>
                                <td>
                                    <div class="btn-group">
                                        <a href="{{ route('assignments.show', $assignment) }}" class="btn btn-sm btn-info">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <a href="{{ route('assignments.edit', $assignment) }}" class="btn btn-sm btn-warning">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <form action="{{ route('assignments.destroy', $assignment) }}" method="POST" 
                                            onsubmit="return confirm('Êtes-vous sûr de vouloir supprimer cette mission ?');"
                                            style="display: inline-block;">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-danger">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="8" class="text-center">Aucune mission trouvée</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="card-footer clearfix">
                    <div class="float-right">
                        {{ $assignments->appends(request()->query())->onEachSide(2)->links('pagination.bootstrap-4') }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
