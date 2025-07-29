@extends('layouts.adminlte')

@section('title')
Influenceur: {{ $influencer->name }}
@endsection

@section('breadcrumb')
<li class="breadcrumb-item"><a href="{{ route('influencers.index') }}">Influenceurs</a></li>
<li class="breadcrumb-item active">{{ $influencer->name }}</li>
@endsection

@section('content')
<div class="row">
    <div class="col-md-4">
        <!-- Profile Card -->
        <div class="card card-primary card-outline">
            <div class="card-body box-profile">
                <div class="text-center">
                    @if($influencer->profile_photo)
                        <img class="profile-user-img img-fluid img-circle" src="{{ asset('storage/' . $influencer->profile_photo) }}" alt="Photo de profil">
                    @else
                        <div class="profile-user-img img-fluid img-circle bg-light d-flex align-items-center justify-content-center">
                            <i class="fas fa-user fa-2x text-secondary"></i>
                        </div>
                    @endif
                </div>

                <h3 class="profile-username text-center">{{ $influencer->name }}</h3>
                <p class="text-muted text-center">Influenceur</p>

                <ul class="list-group list-group-unbordered mb-3">
                    <li class="list-group-item">
                        <b>Pseudo</b> <a class="float-right">{{ $influencer->pseudo ?? 'Non renseigné' }}</a>
                    </li>
                    <li class="list-group-item">
                        <b>Email</b> <a class="float-right">{{ $influencer->email }}</a>
                    </li>
                    <li class="list-group-item">
                        <b>Téléphone</b> <a class="float-right">{{ $influencer->phone ?? 'Non renseigné' }}</a>
                    </li>
                    <li class="list-group-item">
                        <b>Ville</b> <a class="float-right">{{ $influencer->city ?? 'Non renseigné' }}</a>
                    </li>
                    <li class="list-group-item">
                        <b>Date de naissance</b> <a class="float-right">{{ $influencer->birthdate ? $influencer->birthdate->format('d/m/Y') : 'Non renseigné' }}</a>
                    </li>
                    <li class="list-group-item">
                        <b>Missions</b> <a class="float-right">{{ $assignments->count() }}</a>
                    </li>
                    <li class="list-group-item">
                        <b>Statut</b> 
                        <span class="float-right">
                            @if($influencer->is_active)
                                <span class="badge badge-success">Actif</span>
                            @else
                                <span class="badge badge-secondary">Inactif</span>
                            @endif
                        </span>
                    </li>
                </ul>

                @can('update', $influencer)
                <a href="{{ route('influencers.edit', $influencer) }}" class="btn btn-primary btn-block"><i class="fas fa-edit mr-1"></i> Modifier</a>
                @endcan

                @if($influencer->is_active)
                <a href="{{ route('influencers.assign-campaigns', $influencer) }}" class="btn btn-info btn-block mt-2"><i class="fas fa-plus-circle mr-1"></i> Assigner à une campagne</a>
                @endif
            </div>
            <!-- /.card-body -->
        </div>
        <!-- /.card -->

        <!-- Social Links Box -->
        <div class="card card-primary">
            <div class="card-header">
                <h3 class="card-title">Réseaux sociaux</h3>
            </div>
            <!-- /.card-header -->
            <div class="card-body">
                @php 
                    $socialLinks = is_string($influencer->social_links) ? json_decode($influencer->social_links, true) : $influencer->social_links; 
                @endphp
                
                @if($socialLinks && count($socialLinks) > 0)
                    <ul class="list-group list-group-unbordered">
                        @foreach($socialLinks as $platform => $link)
                            @if(!empty($link))
                                <li class="list-group-item">
                                    <b>
                                        @switch(strtolower($platform))
                                            @case('instagram')
                                                <i class="fab fa-instagram text-pink mr-1"></i> Instagram
                                                @break
                                            @case('youtube')
                                                <i class="fab fa-youtube text-danger mr-1"></i> YouTube
                                                @break
                                            @case('tiktok')
                                                <i class="fab fa-tiktok mr-1"></i> TikTok
                                                @break
                                            @case('facebook')
                                                <i class="fab fa-facebook text-primary mr-1"></i> Facebook
                                                @break
                                            @case('twitter')
                                                <i class="fab fa-twitter text-info mr-1"></i> Twitter
                                                @break
                                            @case('linkedin')
                                                <i class="fab fa-linkedin text-primary mr-1"></i> LinkedIn
                                                @break
                                            @case('pinterest')
                                                <i class="fab fa-pinterest text-danger mr-1"></i> Pinterest
                                                @break
                                            @case('snapchat')
                                                <i class="fab fa-snapchat text-warning mr-1"></i> Snapchat
                                                @break
                                            @default
                                                <i class="fas fa-link mr-1"></i> {{ ucfirst($platform) }}
                                        @endswitch
                                    </b> 
                                    <a href="{{ $link }}" target="_blank" class="float-right">Voir <i class="fas fa-external-link-alt fa-xs"></i></a>
                                </li>
                            @endif
                        @endforeach
                    </ul>
                @else
                    <p class="text-muted">Aucun réseau social enregistré.</p>
                @endif
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
                    <li class="nav-item"><a class="nav-link" href="#publications" data-toggle="tab">Publications</a></li>
                    <li class="nav-item"><a class="nav-link" href="#stats" data-toggle="tab">Statistiques</a></li>
                </ul>
            </div><!-- /.card-header -->
            <div class="card-body">
                <div class="tab-content">
                    <div class="active tab-pane" id="campaigns">
                        @if($assignments->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-hover table-striped">
                                <thead>
                                    <tr>
                                        <th>Campagne</th>
                                        <th>Statut</th>
                                        <th>Échéance</th>
                                        <th>Postes requis</th>
                                        <th>Postes publiés</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($assignments as $assignment)
                                    <tr>
                                        <td>{{ $assignment->campaign->name }}</td>
                                        <td>
                                            @php
                                                $status = $assignment->status;
                                                $badgeClass = 'secondary';
                                                switch($status) {
                                                    case 'pending':
                                                        $badgeClass = 'warning';
                                                        $statusText = 'En attente';
                                                        break;
                                                    case 'in_progress':
                                                        $badgeClass = 'info';
                                                        $statusText = 'En cours';
                                                        break;
                                                    case 'completed':
                                                        $badgeClass = 'success';
                                                        $statusText = 'Terminée';
                                                        break;
                                                    case 'cancelled':
                                                        $badgeClass = 'danger';
                                                        $statusText = 'Annulée';
                                                        break;
                                                    default:
                                                        $statusText = ucfirst($status);
                                                }
                                            @endphp
                                            <span class="badge badge-{{ $badgeClass }}">{{ $statusText }}</span>
                                        </td>
                                        <td>{{ $assignment->deadline ? $assignment->deadline->format('d/m/Y') : 'Non définie' }}</td>
                                        <td>{{ $assignment->required_posts }}</td>
                                        <td>{{ $assignment->publications->count() }}</td>
                                        <td>
                                            <a href="{{ route('assignments.show', $assignment) }}" class="btn btn-sm btn-info">
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
                            Cet influenceur n'est assigné à aucune campagne pour le moment.
                        </div>
                        @endif
                    </div>
                    <!-- /.tab-pane -->
                    
                    <div class="tab-pane" id="publications">
                        @if($publications->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-hover table-striped">
                                <thead>
                                    <tr>
                                        <th>Date</th>
                                        <th>Campagne</th>
                                        <th>Plateforme</th>
                                        <th>Statut</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($publications as $publication)
                                    <tr>
                                        <td>{{ $publication->published_at ? $publication->published_at->format('d/m/Y H:i') : 'Non publié' }}</td>
                                        <td>{{ $publication->assignment->campaign->name }}</td>
                                        <td>
                                            @switch(strtolower($publication->platform))
                                                @case('instagram')
                                                    <i class="fab fa-instagram text-pink"></i> Instagram
                                                    @break
                                                @case('youtube')
                                                    <i class="fab fa-youtube text-danger"></i> YouTube
                                                    @break
                                                @case('tiktok')
                                                    <i class="fab fa-tiktok"></i> TikTok
                                                    @break
                                                @case('facebook')
                                                    <i class="fab fa-facebook text-primary"></i> Facebook
                                                    @break
                                                @default
                                                    <i class="fas fa-link"></i> {{ ucfirst($publication->platform) }}
                                            @endswitch
                                        </td>
                                        <td>
                                            @php
                                                $status = $publication->status;
                                                $badgeClass = 'secondary';
                                                switch($status) {
                                                    case 'draft':
                                                        $badgeClass = 'secondary';
                                                        $statusText = 'Brouillon';
                                                        break;
                                                    case 'pending':
                                                        $badgeClass = 'warning';
                                                        $statusText = 'En attente';
                                                        break;
                                                    case 'approved':
                                                        $badgeClass = 'success';
                                                        $statusText = 'Approuvé';
                                                        break;
                                                    case 'rejected':
                                                        $badgeClass = 'danger';
                                                        $statusText = 'Rejeté';
                                                        break;
                                                    default:
                                                        $statusText = ucfirst($status);
                                                }
                                            @endphp
                                            <span class="badge badge-{{ $badgeClass }}">{{ $statusText }}</span>
                                        </td>
                                        <td>
                                            <a href="{{ route('publications.show', $publication) }}" class="btn btn-sm btn-info">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            @if($publication->post_url)
                                                <a href="{{ $publication->post_url }}" target="_blank" class="btn btn-sm btn-secondary">
                                                    <i class="fas fa-external-link-alt"></i>
                                                </a>
                                            @endif
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        @else
                        <div class="alert alert-info">
                            Cet influenceur n'a pas encore de publications.
                        </div>
                        @endif
                    </div>
                    <!-- /.tab-pane -->
                    
                    <div class="tab-pane" id="stats">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="info-box">
                                    <span class="info-box-icon bg-info"><i class="fas fa-tasks"></i></span>
                                    <div class="info-box-content">
                                        <span class="info-box-text">Missions totales</span>
                                        <span class="info-box-number">{{ $assignments->count() }}</span>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="info-box">
                                    <span class="info-box-icon bg-success"><i class="fas fa-check-circle"></i></span>
                                    <div class="info-box-content">
                                        <span class="info-box-text">Missions terminées</span>
                                        <span class="info-box-number">{{ $assignments->where('status', 'completed')->count() }}</span>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="info-box">
                                    <span class="info-box-icon bg-warning"><i class="fas fa-file-upload"></i></span>
                                    <div class="info-box-content">
                                        <span class="info-box-text">Publications totales</span>
                                        <span class="info-box-number">{{ $publications->count() }}</span>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="info-box">
                                    <span class="info-box-icon bg-danger"><i class="fas fa-calendar"></i></span>
                                    <div class="info-box-content">
                                        <span class="info-box-text">Temps de réponse moyen</span>
                                        <span class="info-box-number">3 jours</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Graphique des performances (placeholder) -->
                        <div class="card">
                            <div class="card-header">
                                <h3 class="card-title">Performance des publications</h3>
                            </div>
                            <div class="card-body">
                                <div class="text-center text-muted py-5">
                                    <i class="fas fa-chart-bar fa-4x mb-3"></i>
                                    <p>Les données de performance seront disponibles lorsque l'influenceur aura des publications approuvées.</p>
                                </div>
                            </div>
                        </div>
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
