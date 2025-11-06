@extends('layouts.adminlte')

@section('title', 'Gestion des campagnes')

@section('content_header')
<div class="container-fluid">
    <div class="row mb-2">
        <div class="col-sm-6">
            <h1>Campagnes</h1>
        </div>
        <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                <li class="breadcrumb-item active">Campagnes</li>
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
                    <h3 class="card-title">Liste des campagnes</h3>
                    <div class="card-tools">
                        @if(Auth::user()->role !== 'supervisor')
                        <a href="{{ route('campaigns.create') }}" class="btn btn-primary btn-sm">
                            <i class="fas fa-plus"></i> Nouvelle campagne
                        </a>
                        @endif
                    </div>
                </div>
                <div class="card-body">
                    <table class="table table-bordered table-striped">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Titre</th>
                                <th>Statut</th>
                                <th>Début</th>
                                <th>Fin</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($campaigns as $campaign)
                            <tr>
                                <td>{{ $campaign->id }}</td>
                                <td>{{ $campaign->name }}</td>
                                <td>
                                    @switch($campaign->status)
                                        @case('draft')
                                            <span class="badge badge-secondary">Brouillon</span>
                                            @break
                                        @case('active')
                                            <span class="badge badge-success">Active</span>
                                            @break
                                        @case('paused')
                                            <span class="badge badge-warning">Pause</span>
                                            @break
                                        @case('completed')
                                            <span class="badge badge-info">Terminée</span>
                                            @break
                                        @default
                                            <span class="badge badge-secondary">{{ $campaign->status }}</span>
                                    @endswitch
                                </td>
                                <td>{{ $campaign->start_date ? $campaign->start_date->format('d/m/Y') : 'N/A' }}</td>
                                <td>{{ $campaign->end_date ? $campaign->end_date->format('d/m/Y') : 'N/A' }}</td>
                                <td>
                                    <div class="btn-group">
                                        <a href="{{ route('campaigns.show', $campaign) }}" class="btn btn-sm btn-info">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        @if(Auth::user()->role !== 'influencer')
                                        <a href="{{ route('campaigns.edit', $campaign) }}" class="btn btn-sm btn-warning">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <a href="{{ route('campaigns.dashboard', $campaign) }}" class="btn btn-sm btn-primary">
                                            <i class="fas fa-chart-bar"></i>
                                        </a>
                                        @if(Auth::user()->role === 'admin' || Auth::user()->role === 'super_admin')
                                        <form action="{{ route('campaigns.destroy', $campaign) }}" method="POST" 
                                            onsubmit="return confirm('Êtes-vous sûr de vouloir supprimer cette campagne ?');"
                                            style="display: inline-block;">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-danger">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                        @endif
                                        @endif
                                    </div>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="7" class="text-center">Aucune campagne trouvée</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="card-footer clearfix">
                    <div class="float-right">
                        {{ $campaigns->appends(request()->query())->onEachSide(2)->links('pagination.bootstrap-4') }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
