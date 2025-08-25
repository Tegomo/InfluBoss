@extends('layouts.adminlte')

@section('title', 'Gestion des contenus')

@section('content_header')
<div class="container-fluid">
    <div class="row mb-2">
        <div class="col-sm-6">
            <h1>Contenus</h1>
        </div>
        <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                <li class="breadcrumb-item active">Contenus</li>
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
                    <h3 class="card-title">Liste des contenus</h3>
                    <div class="card-tools">
                        @if(Auth::user()->role !== 'supervisor')
                        <a href="{{ route('contents.create') }}" class="btn btn-primary btn-sm">
                            <i class="fas fa-plus"></i> Nouveau contenu
                        </a>
                        @endif
                    </div>
                </div>
                <div class="card-body">
                    <!-- Filtres -->
                    <form action="{{ route('contents.index') }}" method="GET" class="mb-4">
                        <div class="row">
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="campaign_id">Campagne</label>
                                    <select class="form-control" id="campaign_id" name="campaign_id">
                                        <option value="">Toutes les campagnes</option>
                                        @foreach($campaigns as $campaign)
                                            <option value="{{ $campaign->id }}" {{ request('campaign_id') == $campaign->id ? 'selected' : '' }}>
                                                {{ $campaign->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="content_type">Type de contenu</label>
                                    <select class="form-control" id="content_type" name="content_type">
                                        <option value="">Tous les types</option>
                                        <option value="image" {{ request('content_type') == 'image' ? 'selected' : '' }}>Image</option>
                                        <option value="video" {{ request('content_type') == 'video' ? 'selected' : '' }}>Vidéo</option>
                                        <option value="document" {{ request('content_type') == 'document' ? 'selected' : '' }}>Document</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="search">Recherche</label>
                                    <input type="text" class="form-control" id="search" name="search" 
                                        placeholder="Rechercher par titre..." value="{{ request('search') }}">
                                </div>
                            </div>
                            <div class="col-md-2 d-flex align-items-end">
                                <button type="submit" class="btn btn-primary mr-2">Filtrer</button>
                                <a href="{{ route('contents.index') }}" class="btn btn-secondary">Réinitialiser</a>
                            </div>
                        </div>
                    </form>

                    <!-- Tableau des contenus -->
                    <table class="table table-bordered table-striped">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Aperçu</th>
                                <th>Titre</th>
                                <th>Campagne</th>
                                <th>Type</th>
                                <th>Date de création</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($contents as $content)
                            <tr>
                                <td>{{ $content->id }}</td>
                                <td class="text-center">
                                    @if($content->type == 'image')
                                        <img src="{{ asset('storage/' . $content->file_path) }}" alt="{{ $content->title }}" 
                                            style="max-height: 50px; max-width: 100px;" class="img-thumbnail">
                                    @elseif($content->type == 'video')
                                        <i class="fas fa-film fa-2x text-primary"></i>
                                    @else
                                        <i class="fas fa-file-alt fa-2x text-secondary"></i>
                                    @endif
                                </td>
                                <td>{{ $content->title }}</td>
                                <td>{{ $content->campaign->name ?? 'N/A' }}</td>
                                <td>
                                    @switch($content->type)
                                        @case('image')
                                            <span class="badge badge-info">Image</span>
                                            @break
                                        @case('video')
                                            <span class="badge badge-danger">Vidéo</span>
                                            @break
                                        @case('document')
                                            <span class="badge badge-secondary">Document</span>
                                            @break
                                        @default
                                            <span class="badge badge-secondary">{{ $content->type }}</span>
                                    @endswitch
                                </td>
                                <td>{{ $content->created_at->format('d/m/Y') }}</td>
                                <td>
                                    <div class="btn-group">
                                        <a href="{{ route('contents.show', $content) }}" class="btn btn-sm btn-info">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <a href="{{ route('contents.edit', $content) }}" class="btn btn-sm btn-warning">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <a href="{{ route('contents.download', $content) }}" class="btn btn-sm btn-success">
                                            <i class="fas fa-download"></i>
                                        </a>
                                        <form action="{{ route('contents.destroy', $content) }}" method="POST" 
                                              onsubmit="return confirm('Êtes-vous sûr de vouloir supprimer ce contenu ?');"
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
                                <td colspan="7" class="text-center">Aucun contenu trouvé</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="card-footer clearfix">
                    <div class="float-right">
                        {{ $contents->appends(request()->query())->onEachSide(2)->links('pagination.bootstrap-4') }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
