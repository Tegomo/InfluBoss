@extends('layouts.adminlte')

@section('title', 'Détails de l\'utilisateur')

@section('breadcrumb')
<li class="breadcrumb-item"><a href="{{ route('users.index') }}">Utilisateurs</a></li>
<li class="breadcrumb-item active">Détails</li>
@endsection

@section('content')
<div class="row">
    <div class="col-md-4">
        <!-- Profile Image -->
        <div class="card card-primary card-outline">
            <div class="card-body box-profile">
                <div class="text-center">
                    <div class="bg-light rounded-circle mx-auto d-flex align-items-center justify-content-center" style="width: 100px; height: 100px;">
                        <i class="fas fa-user fa-3x text-secondary"></i>
                    </div>
                </div>

                <h3 class="profile-username text-center">{{ $user->name }}</h3>

                <p class="text-muted text-center">{{ $user->email }}</p>

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
                <div class="text-center mb-3">
                    <span class="badge badge-{{ $badgeClass }} px-3 py-2">{{ ucfirst($user->role) }}</span>
                </div>

                <ul class="list-group list-group-unbordered mb-3">
                    @if($user->pseudo)
                    <li class="list-group-item">
                        <b>Pseudo</b> <a class="float-right">{{ $user->pseudo }}</a>
                    </li>
                    @endif
                    @if($user->birthdate)
                    <li class="list-group-item">
                        <b>Date de naissance</b> <a class="float-right">{{ $user->birthdate->format('d/m/Y') }}</a>
                    </li>
                    @endif
                    @if($user->phone)
                    <li class="list-group-item">
                        <b>Téléphone</b> <a class="float-right">{{ $user->phone }}</a>
                    </li>
                    @endif
                    @if($user->city)
                    <li class="list-group-item">
                        <b>Ville</b> <a class="float-right">{{ $user->city }}</a>
                    </li>
                    @endif
                    <li class="list-group-item">
                        <b>Date d'inscription</b> <a class="float-right">{{ $user->created_at->format('d/m/Y') }}</a>
                    </li>
                    <li class="list-group-item">
                        <b>Dernière mise à jour</b> <a class="float-right">{{ $user->updated_at->format('d/m/Y') }}</a>
                    </li>
                    <li class="list-group-item">
                        <b>Campagnes assignées</b> <a class="float-right">{{ 
                            $user->role === 'influencer' ? $user->assignedCampaigns->count() : 
                            ($user->role === 'supervisor' ? $user->supervisedCampaigns->count() : 
                             ($user->role === 'admin' || $user->role === 'super_admin' ? $user->createdCampaigns->count() : 0)) 
                        }}</a>
                    </li>
                </ul>

                <div class="d-flex justify-content-between">
                    @can('update', $user)
                    <a href="{{ route('users.edit', $user) }}" class="btn btn-primary">
                        <i class="fas fa-edit mr-1"></i> Modifier
                    </a>
                    @endcan
                    <a href="{{ route('users.index') }}" class="btn btn-secondary">
                        <i class="fas fa-arrow-left mr-1"></i> Retour
                    </a>
                </div>
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
                    <li class="nav-item"><a class="nav-link active" href="#campaigns" data-toggle="tab">Campagnes</a></li>
                    <li class="nav-item"><a class="nav-link" href="#activity" data-toggle="tab">Activité</a></li>
                </ul>
            </div><!-- /.card-header -->
            <div class="card-body">
                <div class="tab-content">
                    <div class="active tab-pane" id="campaigns">
                        @php
                            $campaigns = [];
                            if ($user->role === 'influencer') {
                                $campaigns = $user->assignedCampaigns;
                            } elseif ($user->role === 'supervisor') {
                                $campaigns = $user->supervisedCampaigns;
                            } elseif ($user->role === 'admin' || $user->role === 'super_admin') {
                                $campaigns = $user->createdCampaigns;
                            }
                        @endphp
                        @if(count($campaigns) > 0)
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Nom</th>
                                        <th>Statut</th>
                                        <th>Date de début</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($campaigns as $campaign)
                                    <tr>
                                        <td>{{ $campaign->name }}</td>
                                        <td>
                                            @php
                                                $statusClass = 'secondary';
                                                $statusText = 'Inconnu';
                                                
                                                if(isset($campaign->status)) {
                                                    switch($campaign->status) {
                                                        case 'active':
                                                            $statusClass = 'success';
                                                            $statusText = 'Active';
                                                            break;
                                                        case 'pending':
                                                            $statusClass = 'warning';
                                                            $statusText = 'En attente';
                                                            break;
                                                        case 'completed':
                                                            $statusClass = 'info';
                                                            $statusText = 'Terminée';
                                                            break;
                                                        case 'cancelled':
                                                            $statusClass = 'danger';
                                                            $statusText = 'Annulée';
                                                            break;
                                                    }
                                                }
                                            @endphp
                                            <span class="badge badge-{{ $statusClass }}">{{ $statusText }}</span>
                                        </td>
                                        <td>{{ isset($campaign->start_date) ? $campaign->start_date->format('d/m/Y') : 'Non définie' }}</td>
                                        <td>
                                            <a href="{{ route('campaigns.show', $campaign) }}" class="btn btn-info btn-sm">
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
                            <i class="icon fas fa-info-circle"></i> Aucune campagne assignée à cet utilisateur.
                        </div>
                        @endif
                    </div>
                    <!-- /.tab-pane -->
                    <div class="tab-pane" id="activity">
                        @if(isset($user->influencerAssignments) && $user->influencerAssignments->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Campagne</th>
                                        <th>Statut</th>
                                        <th>Date d'assignation</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($user->influencerAssignments as $assignment)
                                    <tr>
                                        <td>{{ $assignment->campaign->name }}</td>
                                        <td>
                                            @php
                                                $statusClass = 'secondary';
                                                $statusText = 'Inconnu';
                                                
                                                switch($assignment->status) {
                                                    case 'pending':
                                                        $statusClass = 'warning';
                                                        $statusText = 'En attente';
                                                        break;
                                                    case 'approved':
                                                        $statusClass = 'success';
                                                        $statusText = 'Approuvé';
                                                        break;
                                                    case 'rejected':
                                                        $statusClass = 'danger';
                                                        $statusText = 'Refusé';
                                                        break;
                                                    case 'completed':
                                                        $statusClass = 'info';
                                                        $statusText = 'Terminé';
                                                        break;
                                                }
                                            @endphp
                                            <span class="badge badge-{{ $statusClass }}">{{ $statusText }}</span>
                                        </td>
                                        <td>{{ $assignment->created_at->format('d/m/Y') }}</td>
                                        <td>
                                            <a href="#" class="btn btn-info btn-sm">
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
                            <i class="icon fas fa-info-circle"></i> Aucune activité enregistrée pour cet utilisateur.
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
@endsection
