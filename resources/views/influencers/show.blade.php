@extends('layouts.adminlte')

@section('title')
Ambassadeur : {{ $influencer->name }}
@endsection

@section('breadcrumb')
<li class="breadcrumb-item"><a href="{{ route('influencers.index') }}">Ambassadeurs</a></li>
<li class="breadcrumb-item active">{{ $influencer->name }}</li>
@endsection

@section('content')
@if(session('success'))
<div class="alert alert-success alert-dismissible">
    <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
    {{ session('success') }}
</div>
@endif
@if($errors->any())
<div class="alert alert-danger alert-dismissible">
    <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
    <ul class="mb-0">
        @foreach($errors->all() as $error)
            <li>{{ $error }}</li>
        @endforeach
    </ul>
</div>
@endif

<div class="row">
    <div class="col-md-4">
        {{-- Profil --}}
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
                <p class="text-muted text-center mb-2">Ambassadeur</p>
                <p class="text-center">
                    @forelse($influencer->category_labels as $label)
                        <span class="badge badge-info mr-1">{{ $label }}</span>
                    @empty
                        <span class="text-muted small">Aucune catégorie</span>
                    @endforelse
                </p>

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
                        <b>Adresse</b> <a class="float-right text-right">{{ $influencer->address ?? 'Non renseignée' }}</a>
                    </li>
                    <li class="list-group-item">
                        <b>Ville</b> <a class="float-right">{{ $influencer->city ?? 'Non renseignée' }}</a>
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

                @if($influencer->bio)
                    <p class="text-muted small">{{ $influencer->bio }}</p>
                @endif

                @can('update', $influencer)
                <a href="{{ route('influencers.edit', $influencer) }}" class="btn btn-primary btn-block"><i class="fas fa-edit mr-1"></i> Modifier</a>
                @endcan

                @if($influencer->is_active && in_array(auth()->user()->role, ['admin', 'super_admin']))
                <a href="{{ route('assignments.create') }}" class="btn btn-info btn-block mt-2"><i class="fas fa-plus-circle mr-1"></i> Créer une mission</a>
                @endif
            </div>
        </div>

        {{-- Réseaux sociaux et abonnés --}}
        <div class="card card-primary">
            <div class="card-header">
                <h3 class="card-title">Réseaux sociaux et abonnés</h3>
            </div>
            <div class="card-body">
                @if($platformCards->isNotEmpty())
                    <ul class="list-group list-group-unbordered mb-3">
                        @foreach($platformCards as $card)
                            @php $account = $card['account']; @endphp
                            <li class="list-group-item">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <i class="{{ $account->config['icon'] }} mr-1" style="color: {{ $account->config['color'] }}"></i>
                                        <b>{{ $account->label }}</b>
                                        @if($account->handle)
                                            <span class="text-muted small ml-1">{{ '@' . $account->handle }}</span>
                                        @endif
                                        @if($account->url)
                                            <a href="{{ $account->url }}" target="_blank" rel="noopener" class="ml-1" title="Ouvrir le profil"><i class="fas fa-external-link-alt fa-xs"></i></a>
                                        @endif
                                    </div>
                                    <div class="text-right">
                                        <span class="font-weight-bold">{{ $account->followers_count !== null ? number_format($account->followers_count) : '—' }}</span>
                                        <span class="text-muted small">abonnés</span>
                                        @if($card['delta'] !== null)
                                            @if($card['delta'] > 0)
                                                <span class="badge badge-success ml-1">+{{ number_format($card['delta']) }}</span>
                                            @elseif($card['delta'] < 0)
                                                <span class="badge badge-danger ml-1">-{{ number_format(abs($card['delta'])) }}</span>
                                            @else
                                                <span class="badge badge-secondary ml-1">=</span>
                                            @endif
                                        @endif
                                    </div>
                                </div>
                                @if($account->last_synced_at)
                                    <small class="text-muted">Mis à jour le {{ $account->last_synced_at->format('d/m/Y') }}</small>
                                @endif
                            </li>
                        @endforeach
                    </ul>
                    <p class="mb-0">
                        <b>Abonnés totaux</b>
                        <span class="float-right font-weight-bold">{{ number_format($influencer->total_followers) }}</span>
                    </p>
                @else
                    <p class="text-muted mb-0">Aucun réseau social enregistré.</p>
                @endif

                @can('updateFollowers', $influencer)
                    @if($platformCards->isNotEmpty())
                    <hr>
                    <form method="POST" action="{{ route('influencers.update-followers', $influencer) }}">
                        @csrf
                        <p class="mb-2"><b>Mettre à jour les abonnés</b></p>
                        @foreach($platformCards as $card)
                            @php $account = $card['account']; @endphp
                            <div class="form-group mb-2">
                                <div class="input-group input-group-sm">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text" style="min-width: 7rem"><i class="{{ $account->config['icon'] }} mr-1"></i> {{ $account->label }}</span>
                                    </div>
                                    <input type="number" min="0" step="1" class="form-control @error("followers.{$account->platform}") is-invalid @enderror" name="followers[{{ $account->platform }}]" placeholder="Nouveau nombre" value="{{ old("followers.{$account->platform}") }}" aria-label="Abonnés {{ $account->label }}">
                                </div>
                            </div>
                        @endforeach
                        <button type="submit" class="btn btn-sm btn-outline-primary btn-block"><i class="fas fa-sync-alt mr-1"></i> Enregistrer le relevé</button>
                    </form>
                    @endif
                @endcan
            </div>
        </div>
    </div>

    <div class="col-md-8">
        <div class="card">
            <div class="card-header p-2">
                <ul class="nav nav-pills">
                    <li class="nav-item"><a class="nav-link active" href="#campaigns" data-toggle="tab">Campagnes</a></li>
                    <li class="nav-item"><a class="nav-link" href="#publications" data-toggle="tab">Publications</a></li>
                    <li class="nav-item"><a class="nav-link" href="#stats" data-toggle="tab">Statistiques</a></li>
                </ul>
            </div>
            <div class="card-body">
                <div class="tab-content">
                    {{-- Historique des campagnes avec résultats --}}
                    <div class="active tab-pane" id="campaigns">
                        @if($campaignResults->isNotEmpty())
                        <p class="text-muted small">Résultats calculés sur le dernier relevé de chaque publication vérifiée.</p>
                        <div class="table-responsive">
                            <table class="table table-hover table-striped table-sm">
                                <thead>
                                    <tr>
                                        <th>Campagne</th>
                                        <th>Statut</th>
                                        <th>Échéance</th>
                                        <th title="vérifiées / publiées / requises">Publications</th>
                                        <th class="text-right">Likes</th>
                                        <th class="text-right">Commentaires</th>
                                        <th class="text-right">Partages</th>
                                        <th class="text-right">Vues</th>
                                        <th class="text-right">Engagement</th>
                                        <th class="text-right">Taux</th>
                                        <th></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($campaignResults as $result)
                                    @php $assignment = $result['assignment']; @endphp
                                    <tr>
                                        <td>
                                            @if($result['campaign'])
                                                <a href="{{ route('campaigns.show', $result['campaign']) }}">{{ $result['campaign']->name }}</a>
                                                <br><small class="text-muted">{{ $result['campaign']->start_date?->format('d/m/Y') }} – {{ $result['campaign']->end_date?->format('d/m/Y') }}</small>
                                            @else
                                                <span class="text-muted">Campagne supprimée</span>
                                            @endif
                                        </td>
                                        <td>
                                            @php
                                                [$badgeClass, $statusText] = match($assignment->status) {
                                                    'pending' => ['warning', 'En attente'],
                                                    'accepted' => ['info', 'Acceptée'],
                                                    'in_progress' => ['info', 'En cours'],
                                                    'declined' => ['danger', 'Refusée'],
                                                    'completed' => ['success', 'Terminée'],
                                                    default => ['secondary', ucfirst((string) $assignment->status)],
                                                };
                                            @endphp
                                            <span class="badge badge-{{ $badgeClass }}">{{ $statusText }}</span>
                                        </td>
                                        <td>{{ $assignment->deadline ? $assignment->deadline->format('d/m/Y') : '—' }}</td>
                                        <td>{{ $result['verified'] }} / {{ $result['published'] }} / {{ $result['required_posts'] }}</td>
                                        <td class="text-right">{{ number_format($result['likes']) }}</td>
                                        <td class="text-right">{{ number_format($result['comments']) }}</td>
                                        <td class="text-right">{{ number_format($result['shares']) }}</td>
                                        <td class="text-right">{{ number_format($result['views']) }}</td>
                                        <td class="text-right">{{ number_format($result['engagement']) }}</td>
                                        <td class="text-right">{{ $result['engagement_rate'] !== null ? number_format($result['engagement_rate'], 2) . ' %' : '—' }}</td>
                                        <td>
                                            <a href="{{ route('assignments.show', $assignment) }}" class="btn btn-sm btn-info" title="Voir la mission">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                                <tfoot>
                                    <tr class="font-weight-bold">
                                        <th colspan="3">Total</th>
                                        <th>{{ $campaignTotals['verified'] }} / {{ $campaignTotals['published'] }} / {{ $campaignTotals['required_posts'] }}</th>
                                        <th class="text-right">{{ number_format($campaignTotals['likes']) }}</th>
                                        <th class="text-right">{{ number_format($campaignTotals['comments']) }}</th>
                                        <th class="text-right">{{ number_format($campaignTotals['shares']) }}</th>
                                        <th class="text-right">{{ number_format($campaignTotals['views']) }}</th>
                                        <th class="text-right">{{ number_format($campaignTotals['engagement']) }}</th>
                                        <th class="text-right">{{ $campaignTotals['engagement_rate'] !== null ? number_format($campaignTotals['engagement_rate'], 2) . ' %' : '—' }}</th>
                                        <th></th>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                        @else
                        <div class="alert alert-info mb-0">
                            Cet ambassadeur n'a encore participé à aucune campagne.
                        </div>
                        @endif
                    </div>

                    {{-- Publications --}}
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
                                    @php $platformConfig = config('influboss.platforms.' . strtolower($publication->platform)); @endphp
                                    <tr>
                                        <td>{{ $publication->published_at ? $publication->published_at->format('d/m/Y H:i') : 'Non publié' }}</td>
                                        <td>{{ $publication->assignment?->campaign?->name ?? '—' }}</td>
                                        <td>
                                            @if($platformConfig)
                                                <i class="{{ $platformConfig['icon'] }}" style="color: {{ $platformConfig['color'] }}"></i> {{ $platformConfig['label'] }}
                                            @else
                                                <i class="fas fa-link"></i> {{ ucfirst($publication->platform) }}
                                            @endif
                                        </td>
                                        <td>
                                            @php
                                                [$badgeClass, $statusText] = match($publication->status) {
                                                    'pending_verification' => ['warning', 'En attente de vérification'],
                                                    'verified' => ['success', 'Vérifiée'],
                                                    'rejected' => ['danger', 'Rejetée'],
                                                    default => ['secondary', ucfirst((string) $publication->status)],
                                                };
                                            @endphp
                                            <span class="badge badge-{{ $badgeClass }}">{{ $statusText }}</span>
                                        </td>
                                        <td>
                                            <a href="{{ route('publications.show', $publication) }}" class="btn btn-sm btn-info">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            @if($publication->post_url)
                                                <a href="{{ $publication->post_url }}" target="_blank" rel="noopener" class="btn btn-sm btn-secondary">
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
                        <div class="alert alert-info mb-0">
                            Cet ambassadeur n'a pas encore de publications.
                        </div>
                        @endif
                    </div>

                    {{-- Statistiques --}}
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
                                    <span class="info-box-icon bg-danger"><i class="fas fa-users"></i></span>
                                    <div class="info-box-content">
                                        <span class="info-box-text">Abonnés totaux</span>
                                        <span class="info-box-number">{{ number_format($influencer->total_followers) }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Évolution des abonnés --}}
                        <div class="card">
                            <div class="card-header">
                                <h3 class="card-title">Évolution des abonnés (12 derniers mois)</h3>
                            </div>
                            <div class="card-body">
                                @if(count($followerChart['datasets']) > 0)
                                    <canvas id="followersChart" height="110"></canvas>
                                @else
                                    <div class="text-center text-muted py-4">
                                        <i class="fas fa-chart-line fa-3x mb-3"></i>
                                        <p class="mb-0">Aucun historique d'abonnés pour le moment. Les relevés saisis apparaîtront ici.</p>
                                    </div>
                                @endif
                            </div>
                        </div>

                        {{-- Performance des publications vérifiées --}}
                        <div class="card">
                            <div class="card-header">
                                <h3 class="card-title">Performance des publications</h3>
                            </div>
                            <div class="card-body">
                                @php
                                    $verifiedPublications = $publications->where('status', 'verified')->filter(fn ($pub) => $pub->latestStats !== null);
                                @endphp

                                @if($verifiedPublications->isNotEmpty())
                                    <div class="row">
                                        <div class="col-md-3">
                                            <div class="info-box bg-info">
                                                <span class="info-box-icon"><i class="fas fa-heart"></i></span>
                                                <div class="info-box-content">
                                                    <span class="info-box-text">Likes totaux</span>
                                                    <span class="info-box-number">{{ number_format($campaignTotals['likes']) }}</span>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="info-box bg-success">
                                                <span class="info-box-icon"><i class="fas fa-comments"></i></span>
                                                <div class="info-box-content">
                                                    <span class="info-box-text">Commentaires</span>
                                                    <span class="info-box-number">{{ number_format($campaignTotals['comments']) }}</span>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="info-box bg-warning">
                                                <span class="info-box-icon"><i class="fas fa-share"></i></span>
                                                <div class="info-box-content">
                                                    <span class="info-box-text">Partages</span>
                                                    <span class="info-box-number">{{ number_format($campaignTotals['shares']) }}</span>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="info-box bg-danger">
                                                <span class="info-box-icon"><i class="fas fa-eye"></i></span>
                                                <div class="info-box-content">
                                                    <span class="info-box-text">Vues totales</span>
                                                    <span class="info-box-number">{{ number_format($campaignTotals['views']) }}</span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="table-responsive mt-3">
                                        <table class="table table-striped table-sm">
                                            <thead>
                                                <tr>
                                                    <th>Publication</th>
                                                    <th>Plateforme</th>
                                                    <th class="text-right">Likes</th>
                                                    <th class="text-right">Commentaires</th>
                                                    <th class="text-right">Partages</th>
                                                    <th class="text-right">Vues</th>
                                                    <th class="text-right">Taux d'engagement</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach($verifiedPublications as $pub)
                                                <tr>
                                                    <td><a href="{{ route('publications.show', $pub) }}">#{{ $pub->id }}</a></td>
                                                    <td>{{ ucfirst($pub->platform) }}</td>
                                                    <td class="text-right">{{ number_format($pub->latestStats->likes) }}</td>
                                                    <td class="text-right">{{ number_format($pub->latestStats->comments) }}</td>
                                                    <td class="text-right">{{ number_format($pub->latestStats->shares) }}</td>
                                                    <td class="text-right">{{ number_format($pub->latestStats->views ?? 0) }}</td>
                                                    <td class="text-right">{{ number_format($pub->latestStats->engagement_rate, 2) }} %</td>
                                                </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                @else
                                    <div class="text-center text-muted py-4">
                                        <i class="fas fa-chart-bar fa-3x mb-3"></i>
                                        <p class="mb-0">Les données de performance apparaîtront lorsque l'ambassadeur aura des publications vérifiées avec des statistiques.</p>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
@if(count($followerChart['datasets']) > 0)
<script src="https://cdn.jsdelivr.net/npm/chart.js@3.7.0/dist/chart.min.js"></script>
<script>
    $(function () {
        const canvas = document.getElementById('followersChart');
        if (!canvas || typeof Chart === 'undefined') return;
        new Chart(canvas, {
            type: 'line',
            data: @json($followerChart),
            options: {
                responsive: true,
                interaction: { mode: 'index', intersect: false },
                plugins: { legend: { position: 'bottom' } },
                scales: { y: { beginAtZero: true, ticks: { precision: 0 } } }
            }
        });
    });
</script>
@endif
@endpush
