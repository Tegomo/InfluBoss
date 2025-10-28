@extends('layouts.adminlte')

@section('title')
@if(isset($selectedRole) && $selectedRole === 'influencer')
    Liste des Ambassadeurs
@elseif(isset($selectedRole) && $selectedRole === 'supervisor')
    Liste des superviseurs
@else
    Liste des utilisateurs
@endif
@endsection

@section('breadcrumb')
@if(isset($selectedRole) && $selectedRole === 'influencer')
    <li class="breadcrumb-item active">Ambassadeurs</li>
@elseif(isset($selectedRole) && $selectedRole === 'supervisor')
    <li class="breadcrumb-item active">Superviseurs</li>
@else
    <li class="breadcrumb-item active">Utilisateurs</li>
@endif
@endsection

@section('content')
<div class="card">
    <div class="card-header">
        <h3 class="card-title">
            @if(isset($selectedRole) && $selectedRole === 'influencer')
                Liste des Ambassadeurs
            @elseif(isset($selectedRole) && $selectedRole === 'supervisor')
                Liste des superviseurs
            @else
                Liste des utilisateurs
            @endif
        </h3>
        <div class="card-tools">
            @can('create', \App\Models\User::class)
            <a href="{{ route('users.create') }}" class="btn btn-primary btn-sm">
                <i class="fas fa-plus mr-1"></i> Ajouter un utilisateur
            </a>
            @endcan
            
            @if(isset($selectedRole) && $selectedRole === 'influencer' && (auth()->user()->role === 'admin' || auth()->user()->role === 'super_admin'))
            <a href="{{ route('influencers.import-export') }}" class="btn btn-success btn-sm ml-2">
                <i class="fas fa-file-csv mr-1"></i> Import/Export CSV
            </a>
            @endif
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

        <table class="table table-hover text-nowrap">
            <thead>
                <tr>
                    <th>Nom</th>
                    <th>Email</th>
                    <th>Rôle</th>
                    <th>Créé le</th>
                    <th class="text-right">Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach($users as $user)
                <tr>
                    <td>
                        <div class="d-flex align-items-center">
                            <div class="mr-3">
                                <div class="bg-light rounded-circle p-2" style="width: 40px; height: 40px; display: flex; align-items: center; justify-content: center;">
                                    <i class="fas fa-user text-secondary"></i>
                                </div>
                            </div>
                            <span>{{ $user->name }}</span>
                        </div>
                    </td>
                    <td>{{ $user->email }}</td>
                    <td>
                        @php
                            $badgeClass = 'secondary';
                            switch($user->role) {
                                case 'super_admin':
                                    $badgeClass = 'danger';
                                    break;
                                case 'admin':
                                    $badgeClass = 'primary';
                                    break;
                                case 'supervisor':
                                    $badgeClass = 'success';
                                    break;
                                case 'influencer':
                                    $badgeClass = 'warning';
                                    break;
                            }
                        @endphp
                        <span class="badge badge-{{ $badgeClass }}">{{ ucfirst($user->role) }}</span>
                    </td>
                    <td>{{ $user->created_at->format('d/m/Y') }}</td>
                    <td class="text-right">
                        <div class="btn-group">
                            <a href="{{ route('users.show', $user) }}" class="btn btn-info btn-sm">
                                <i class="fas fa-eye"></i>
                            </a>
                            @can('update', $user)
                            <a href="{{ route('users.edit', $user) }}" class="btn btn-primary btn-sm">
                                <i class="fas fa-edit"></i>
                            </a>
                            @endcan
                            @can('delete', $user)
                            <form action="{{ route('users.destroy', $user) }}" method="POST" class="d-inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Êtes-vous sûr de vouloir supprimer cet utilisateur ?')">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </form>
                            @endcan
                        </div>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    <!-- /.card-body -->
    <div class="card-footer clearfix">
        <div class="float-right">
            {{ $users->appends(request()->query())->onEachSide(2)->links('pagination.bootstrap-4') }}
        </div>
    </div>
</div>
<!-- /.card -->
@endsection
