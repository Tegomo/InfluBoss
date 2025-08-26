@extends('layouts.adminlte')

@section('title')
Liste des superviseurs
@endsection

@section('breadcrumb')
<li class="breadcrumb-item active">Superviseurs</li>
@endsection

@section('content')
<div class="card">
    <div class="card-header">
        <h3 class="card-title">Liste des superviseurs</h3>
        <div class="card-tools">
            @can('create', \App\Models\User::class)
            <a href="{{ route('supervisors.create') }}" class="btn btn-primary btn-sm">
                <i class="fas fa-plus mr-1"></i> Ajouter un superviseur
            </a>
            @endcan
        </div>
    </div>
    <!-- /.card-header -->
    <div class="card-body table-responsive p-0">
        @if(session('success'))
        <div class="alert alert-success alert-dismissible">
            <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
            <h5><i class="icon fas fa-check"></i> Succès!</h5>
            {{ session('success') }}
        </div>
        @endif

        <!-- Filtres -->
        <div class="p-3 bg-light">
            <form action="{{ route('supervisors.index') }}" method="GET" class="row">
                <div class="col-md-4 mb-2">
                    <input type="text" name="search" class="form-control form-control-sm" placeholder="Rechercher..." value="{{ request('search') }}">
                </div>
                <div class="col-md-4 mb-2">
                    <select name="is_active" class="form-control form-control-sm">
                        <option value="">État (Tous)</option>
                        <option value="1" {{ request('is_active') === '1' ? 'selected' : '' }}>Actifs</option>
                        <option value="0" {{ request('is_active') === '0' ? 'selected' : '' }}>Inactifs</option>
                    </select>
                </div>
                <div class="col-md-4 mb-2">
                    <button type="submit" class="btn btn-sm btn-info mr-1">Filtrer</button>
                    <a href="{{ route('supervisors.index') }}" class="btn btn-sm btn-secondary">Réinitialiser</a>
                </div>
            </form>
        </div>

        <table class="table table-hover text-nowrap">
            <thead>
                <tr>
                    <th>Nom</th>
                    <th>Email</th>
                    <th>Téléphone</th>
                    <th>Ville</th>
                    <th>Ambassadeurs supervisés</th>
                    <th>Statut</th>
                    <th class="text-right">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($supervisors as $supervisor)
                <tr>
                    <td>
                        <div class="d-flex align-items-center">
                            <div class="mr-3">
                                <div class="bg-light rounded-circle p-2" style="width: 40px; height: 40px; display: flex; align-items: center; justify-content: center;">
                                    <i class="fas fa-user-shield text-secondary"></i>
                                </div>
                            </div>
                            <span>{{ $supervisor->name }}</span>
                        </div>
                    </td>
                    <td>{{ $supervisor->email }}</td>
                    <td>{{ $supervisor->phone ?? 'Non renseigné' }}</td>
                    <td>{{ $supervisor->city ?? 'Non renseigné' }}</td>
                    <td>
                        @php
                            $influencerCount = $supervisor->influencers ? $supervisor->influencers->count() : 0;
                        @endphp
                        <span class="badge badge-info">{{ $influencerCount }} ambassadeur(s)</span>
                    </td>
                    <td>
                        @if($supervisor->is_active)
                            <span class="badge badge-success">Actif</span>
                        @else
                            <span class="badge badge-secondary">Inactif</span>
                        @endif
                    </td>
                    <td class="text-right">
                        <a href="{{ route('supervisors.show', $supervisor) }}" class="btn btn-sm btn-info">
                            <i class="fas fa-eye"></i>
                        </a>
                        @can('update', $supervisor)
                        <a href="{{ route('supervisors.edit', $supervisor) }}" class="btn btn-sm btn-primary">
                            <i class="fas fa-edit"></i>
                        </a>
                        @endcan
                        @can('delete', $supervisor)
                        <form action="{{ route('supervisors.destroy', $supervisor) }}" method="POST" class="d-inline" onsubmit="return confirm('Êtes-vous sûr de vouloir supprimer ce superviseur ?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-sm btn-danger">
                                <i class="fas fa-trash"></i>
                            </button>
                        </form>
                        @endcan
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" class="text-center">Aucun superviseur trouvé</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <!-- /.card-body -->
    <div class="card-footer clearfix">
        <div class="float-right">
            {{ $supervisors->appends(request()->query())->onEachSide(2)->links('pagination.bootstrap-4') }}
        </div>
    </div>
</div>
<!-- /.card -->
@endsection
