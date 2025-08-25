@extends('layouts.adminlte')

@section('title')
Superviseur: {{ $supervisor->name }}
@endsection

@section('breadcrumb')
<li class="breadcrumb-item"><a href="{{ route('supervisors.index') }}">Superviseurs</a></li>
<li class="breadcrumb-item active">{{ $supervisor->name }}</li>
@endsection

@section('content')
<div class="row">
    <div class="col-md-4">
        <!-- Profile Card -->
        <div class="card card-primary card-outline">
            <div class="card-body box-profile">
                <div class="text-center">
                    @if($supervisor->profile_photo)
                        <img class="profile-user-img img-fluid img-circle" src="{{ asset('storage/' . $supervisor->profile_photo) }}" alt="Photo de profil">
                    @else
                        <div class="profile-user-img img-fluid img-circle bg-light d-flex align-items-center justify-content-center">
                            <i class="fas fa-user-shield fa-2x text-secondary"></i>
                        </div>
                    @endif
                </div>

                <h3 class="profile-username text-center">{{ $supervisor->name }}</h3>
                <p class="text-muted text-center">Superviseur</p>

                <ul class="list-group list-group-unbordered mb-3">
                    <li class="list-group-item">
                        <b>Email</b> <a class="float-right">{{ $supervisor->email }}</a>
                    </li>
                    <li class="list-group-item">
                        <b>Téléphone</b> <a class="float-right">{{ $supervisor->phone ?? 'Non renseigné' }}</a>
                    </li>
                    <li class="list-group-item">
                        <b>Ville</b> <a class="float-right">{{ $supervisor->city ?? 'Non renseigné' }}</a>
                    </li>
                    <li class="list-group-item">
                        <b>Date de naissance</b> <a class="float-right">{{ $supervisor->birthdate ? $supervisor->birthdate->format('d/m/Y') : 'Non renseigné' }}</a>
                    </li>
                    <li class="list-group-item">
                        <b>Ambassadeurs supervisés</b> <a class="float-right">{{ $influencers->count() }}</a>
                    </li>
                    <li class="list-group-item">
                        <b>Campagnes gérées</b> <a class="float-right">{{ $campaigns->count() }}</a>
                    </li>
                    <li class="list-group-item">
                        <b>Statut</b> 
                        <span class="float-right">
                            @if($supervisor->is_active)
                                <span class="badge badge-success">Actif</span>
                            @else
                                <span class="badge badge-secondary">Inactif</span>
                            @endif
                        </span>
                    </li>
                </ul>

                @can('update', $supervisor)
                <a href="{{ route('supervisors.edit', $supervisor) }}" class="btn btn-primary btn-block"><i class="fas fa-edit mr-1"></i> Modifier</a>
                @endcan
            </div>
            <!-- /.card-body -->
        </div>
        <!-- /.card -->
    </div>
    <!-- /.col -->
    
    <div class="col-md-8">
        <div class="card">
            <div class="card-header p-2">
                <ul class="nav nav-pills">
                    <li class="nav-item"><a class="nav-link active" href="#influencers" data-toggle="tab">Ambassadeurs supervisés</a></li>
                    <li class="nav-item"><a class="nav-link" href="#campaigns" data-toggle="tab">Campagnes gérées</a></li>
                </ul>
            </div><!-- /.card-header -->
            <div class="card-body">
                <div class="tab-content">
                    <div class="active tab-pane" id="influencers">
                        <div class="d-flex justify-content-between mb-3">
                            <h4 class="mb-0">Liste des Ambassadeurs</h4>
                            @can('update', $supervisor)
                            <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#assignInfluencerModal">
                                <i class="fas fa-user-plus mr-1"></i> Assigner un embassadeur
                            </button>
                            @endcan
                        </div>
                        @if($influencers->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-hover table-striped">
                                <thead>
                                    <tr>
                                        <th>Nom</th>
                                        <th>Email</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($influencers as $influencer)
                                    <tr>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <div class="mr-2">
                                                    <div class="bg-light rounded-circle p-1" style="width: 30px; height: 30px; display: flex; align-items: center; justify-content: center;">
                                                        <i class="fas fa-user text-secondary"></i>
                                                    </div>
                                                </div>
                                                <span>{{ $influencer->name }}</span>
                                            </div>
                                        </td>
                                        <td>{{ $influencer->email }}</td>
                                        <td>
                                            <div class="btn-group">
                                                <a href="{{ route('influencers.show', $influencer) }}" class="btn btn-sm btn-info" title="Voir le profil">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                                @can('update', $supervisor)
                                                <form action="{{ route('supervisors.unassign-influencer', [$supervisor, $influencer]) }}" method="POST" class="d-inline" onsubmit="return confirm('Êtes-vous sûr de vouloir retirer cet influenceur de ce superviseur ?');">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-sm btn-danger" title="Retirer cet influenceur">
                                                        <i class="fas fa-user-minus"></i>
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
                        @else
                        <div class="alert alert-info">
                            Ce superviseur ne gère actuellement aucun embassadeur.
                        </div>
                        @endif
                    </div>
                    <!-- /.tab-pane -->
                    
                    <div class="tab-pane" id="campaigns">
                        @if($campaigns->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-hover table-striped">
                                <thead>
                                    <tr>
                                        <th>Nom</th>
                                        <th>Statut</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($campaigns as $campaign)
                                    <tr>
                                        <td>{{ $campaign->name }}</td>
                                        <td>
                                            @php
                                                $status = $campaign->status;
                                                $badgeClass = 'secondary';
                                                switch($status) {
                                                    case 'draft':
                                                        $badgeClass = 'secondary';
                                                        $statusText = 'Brouillon';
                                                        break;
                                                    case 'active':
                                                        $badgeClass = 'success';
                                                        $statusText = 'En cours';
                                                        break;
                                                    case 'paused':
                                                        $badgeClass = 'warning';
                                                        $statusText = 'En pause';
                                                        break;
                                                    case 'completed':
                                                        $badgeClass = 'info';
                                                        $statusText = 'Terminée';
                                                        break;
                                                    case 'canceled':
                                                        $badgeClass = 'danger';
                                                        $statusText = 'Annulée';
                                                        break;
                                                    default:
                                                        $statusText = ucfirst($status);
                                                }
                                            @endphp
                                            <span class="badge badge-{{ $badgeClass }}">{{ $statusText }}</span>
                                        </td>
                                        <td>
                                            <a href="{{ route('campaigns.show', $campaign) }}" class="btn btn-sm btn-info">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        @else
                        <div class="alert alert-info">
                            Ce superviseur ne gère actuellement aucune campagne.
                        </div>
                        @endif
                    </div>
                    <!-- /.tab-pane -->
                </div>
                <!-- /.tab-content -->
            </div><!-- /.card-body -->
        </div>
        <!-- /.card -->
    </div>
    <!-- /.col -->
</div>
<!-- /.row -->

<!-- Modal d'assignation d'influenceur -->
@can('update', $supervisor)
<div class="modal fade" id="assignInfluencerModal" tabindex="-1" role="dialog" aria-labelledby="assignInfluencerModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="assignInfluencerModalLabel">Assigner un influenceur à {{ $supervisor->name }}</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form action="{{ route('supervisors.assign-influencer', $supervisor) }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="form-group">
                        <label for="influencer_id">Sélectionner un influenceur</label>
                        <select class="form-control select2" id="influencer_id" name="influencer_id" required>
                            <option value="">-- Choisir un influenceur --</option>
                            @foreach($unassignedInfluencers as $influencer)
                                <option value="{{ $influencer->id }}">{{ $influencer->name }} ({{ $influencer->email }})</option>
                            @endforeach
                        </select>
                        <small class="form-text text-muted">
                            Cette liste contient uniquement les Ambassadeurs qui ne sont pas encore assignés à un superviseur.
                            Pour réassigner un Embassadeur déjà attribué à un autre superviseur, utilisez l'écran de gestion de cet Embassadeur.
                        </small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Annuler</button>
                    <button type="submit" class="btn btn-primary">Assigner</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endcan

<!-- Initialisation de Select2 pour améliorer l'UX -->
@push('scripts')
<script>
    $(document).ready(function() {
        $('.select2').select2({
            theme: 'bootstrap4',
            placeholder: "-- Choisir un influenceur --",
            allowClear: true
        });
    });
</script>
@endpush

@endsection
