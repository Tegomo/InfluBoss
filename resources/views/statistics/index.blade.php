@extends('layouts.adminlte')

@section('title', 'Statistiques')

@section('content_header')
<div class="container-fluid">
    <div class="row mb-2">
        <div class="col-sm-6">
            <h1>Statistiques globales</h1>
        </div>
        <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                <li class="breadcrumb-item active">Statistiques</li>
            </ol>
        </div>
    </div>
    
</div>
@endsection

@section('content')

<div class="container-fluid">
    @unless(Auth::user()->isClient())
    <!-- Alerte pour l'export global (admins uniquement) -->
    <div class="row mb-3">
        <div class="col-12">
            <div class="alert alert-info d-flex align-items-center justify-content-between">
                <div>
                    <i class="fas fa-database mr-2"></i>
                    <strong>Export complet des données</strong> - Téléchargez toutes les données du site (utilisateurs, campagnes, missions, publications, contenus, statistiques, notes) dans un fichier Excel multi-feuilles
                </div>
                <a href="{{ route('admin.export-all') }}" class="btn btn-primary">
                    <i class="fas fa-download mr-1"></i> Exporter toutes les données
                </a>
            </div>
        </div>
    </div>
    @endunless

    <div class="row">
        <div class="col-sm-12">
            <div class="btn-group float-right gap-2" role="group">
                <a href="{{ route('admin.statistics.export') }}" class="btn btn-success">
                    <i class="fas fa-file-excel mr-1"></i> Exporter Statistiques Excel
                </a>
                <a href="{{ route('admin.statistics.export-pdf') }}" class="btn btn-danger">
                    <i class="fas fa-file-pdf mr-1"></i> Exporter Statistiques PDF
                </a>
                <a href="{{ route('admin.statistics.export-raw-data') }}" class="btn btn-primary">
                    <i class="fas fa-database mr-1"></i> Exporter Données Brutes
                </a>
            </div>
        </div>
    </div>
    <br>

    <!-- Statistiques d'engagement -->
    <div class="row">
        <div class="col-md-12">
            <div class="card card-primary">
                <div class="card-header">
                    <h3 class="card-title">Statistiques d'engagement</h3>
                    <div class="card-tools">
                        <button type="button" class="btn btn-tool" data-card-widget="collapse">
                            <i class="fas fa-minus"></i>
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    <!-- Filtres pour les statistiques d'engagement -->
                    <form method="GET" action="{{ route('admin.statistics') }}" class="mb-4">
                        <div class="row">
                            <div class="col-md-2">
                                <div class="form-group">
                                    <label for="engagement_date_from">Date début</label>
                                    <input type="date" class="form-control form-control-sm" id="engagement_date_from" name="engagement_date_from" value="{{ $engagementFilters['date_from'] ?? '' }}">
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="form-group">
                                    <label for="engagement_date_to">Date fin</label>
                                    <input type="date" class="form-control form-control-sm" id="engagement_date_to" name="engagement_date_to" value="{{ $engagementFilters['date_to'] ?? '' }}">
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="form-group">
                                    <label for="engagement_campaign_id">Campagne</label>
                                    <select class="form-control form-control-sm" id="engagement_campaign_id" name="engagement_campaign_id">
                                        <option value="">Toutes les campagnes</option>
                                        @foreach($campaigns as $campaign)
                                            <option value="{{ $campaign->id }}" {{ ($engagementFilters['campaign_id'] ?? '') == $campaign->id ? 'selected' : '' }}>{{ $campaign->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="form-group">
                                    <label for="engagement_supervisor_id">Superviseur</label>
                                    <select class="form-control form-control-sm" id="engagement_supervisor_id" name="engagement_supervisor_id">
                                        <option value="">Tous les superviseurs</option>
                                        @foreach($supervisors as $supervisor)
                                            <option value="{{ $supervisor->id }}" {{ ($engagementFilters['supervisor_id'] ?? '') == $supervisor->id ? 'selected' : '' }}>{{ $supervisor->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="form-group">
                                    <label for="engagement_influencer_id">Ambassadeur</label>
                                    <select class="form-control form-control-sm" id="engagement_influencer_id" name="engagement_influencer_id">
                                        <option value="">Tous les ambassadeurs</option>
                                        @foreach($influencers as $influencer)
                                            <option value="{{ $influencer->id }}" {{ ($engagementFilters['influencer_id'] ?? '') == $influencer->id ? 'selected' : '' }}>{{ $influencer->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="form-group">
                                    <label>&nbsp;</label>
                                    <div>
                                        <button type="submit" class="btn btn-primary btn-sm">
                                            <i class="fas fa-filter"></i> Filtrer
                                        </button>
                                        <a href="{{ route('admin.statistics.export-engagement', request()->query()) }}" class="btn btn-success btn-sm" title="Exporter CSV">
                                            <i class="fas fa-download"></i>
                                        </a>
                                        <a href="{{ route('admin.statistics') }}" class="btn btn-secondary btn-sm">
                                            <i class="fas fa-times"></i>
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </form>
                    
                    <div class="row">
                        <div class="col-md-3 col-sm-6 col-12">
                            <div class="info-box bg-gradient-success">
                                <span class="info-box-icon"><i class="far fa-thumbs-up"></i></span>
                                <div class="info-box-content">
                                    <span class="info-box-text">Total Likes</span>
                                    <span class="info-box-number">{{ number_format($engagementStats['total']['likes']) }}</span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3 col-sm-6 col-12">
                            <div class="info-box bg-gradient-primary">
                                <span class="info-box-icon"><i class="far fa-comment"></i></span>
                                <div class="info-box-content">
                                    <span class="info-box-text">Total Commentaires</span>
                                    <span class="info-box-number">{{ number_format($engagementStats['total']['comments']) }}</span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3 col-sm-6 col-12">
                            <div class="info-box bg-gradient-warning">
                                <span class="info-box-icon"><i class="fas fa-share"></i></span>
                                <div class="info-box-content">
                                    <span class="info-box-text">Total Partages</span>
                                    <span class="info-box-number">{{ number_format($engagementStats['total']['shares']) }}</span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3 col-sm-6 col-12">
                            <div class="info-box bg-gradient-info">
                                <span class="info-box-icon"><i class="fas fa-eye"></i></span>
                                <div class="info-box-content">
                                    <span class="info-box-text">Total Vues</span>
                                    <span class="info-box-number">{{ number_format($engagementStats['total']['views']) }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="row mt-4">
                        <div class="col-md-12">
                            <div class="card">
                                <div class="card-header">
                                    <h3 class="card-title">Statistiques d'engagement</h3>
                                </div>
                                <div class="card-body">
                                    <div class="row mb-3">
                                        <div class="col-2">Likes</div>
                                        <div class="col-10">
                                            <div class="progress" style="height: 20px;">
                                                @php $maxEngagement = max(1, array_sum($engagementStats['total']) > 0 ? max($engagementStats['total']) : 1); @endphp
                                                <div class="progress-bar bg-primary" role="progressbar" style="width: {{ $engagementStats['total']['likes'] / $maxEngagement * 100 }}%" aria-valuenow="{{ $engagementStats['total']['likes'] }}" aria-valuemin="0" aria-valuemax="{{ $maxEngagement }}">{{ number_format($engagementStats['total']['likes']) }}</div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row mb-3">
                                        <div class="col-2">Commentaires</div>
                                        <div class="col-10">
                                            <div class="progress" style="height: 20px;">
                                                <div class="progress-bar bg-success" role="progressbar" style="width: {{ $engagementStats['total']['comments'] / $maxEngagement * 100 }}%" aria-valuenow="{{ $engagementStats['total']['comments'] }}" aria-valuemin="0" aria-valuemax="{{ $maxEngagement }}">{{ number_format($engagementStats['total']['comments']) }}</div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row mb-3">
                                        <div class="col-2">Partages</div>
                                        <div class="col-10">
                                            <div class="progress" style="height: 20px;">
                                                <div class="progress-bar bg-danger" role="progressbar" style="width: {{ $engagementStats['total']['shares'] / $maxEngagement * 100 }}%" aria-valuenow="{{ $engagementStats['total']['shares'] }}" aria-valuemin="0" aria-valuemax="{{ $maxEngagement }}">{{ number_format($engagementStats['total']['shares']) }}</div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row mb-3">
                                        <div class="col-2">Vues</div>
                                        <div class="col-10">
                                            <div class="progress" style="height: 20px;">
                                                <div class="progress-bar bg-warning" role="progressbar" style="width: {{ $engagementStats['total']['views'] / $maxEngagement * 100 }}%" aria-valuenow="{{ $engagementStats['total']['views'] }}" aria-valuemin="0" aria-valuemax="{{ $maxEngagement }}">{{ number_format($engagementStats['total']['views']) }}</div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-2">Clics</div>
                                        <div class="col-10">
                                            <div class="progress" style="height: 20px;">
                                                <div class="progress-bar bg-info" role="progressbar" style="width: {{ ($engagementStats['total']['clicks'] ?? 0) / $maxEngagement * 100 }}%" aria-valuenow="{{ $engagementStats['total']['clicks'] ?? 0 }}" aria-valuemin="0" aria-valuemax="{{ $maxEngagement }}">{{ number_format($engagementStats['total']['clicks'] ?? 0) }}</div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="row mt-4">
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-header">
                                    <h3 class="card-title">Évolution de l'engagement par plateforme ({{ date('Y') }})</h3>
                                </div>
                                <div class="card-body">
                                    <canvas id="engagementMonthlyChart" style="min-height: 250px; height: 250px; max-height: 250px; max-width: 100%;"></canvas>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-header">
                                    <h3 class="card-title">Engagement par métrique</h3>
                                </div>
                                <div class="card-body">
                                    <canvas id="engagementPlatformChart" style="min-height: 250px; height: 250px; max-height: 250px; max-width: 100%;"></canvas>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Statistiques des utilisateurs -->
    <div class="row">
        <div class="col-md-12">
            <div class="card card-primary">
                <div class="card-header">
                    <h3 class="card-title"><strong>Statistiques des utilisateurs</strong></h3>
                    <div class="card-tools">
                        <button type="button" class="btn btn-tool" data-card-widget="collapse">
                            <i class="fas fa-minus"></i>
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4 col-sm-6 col-12">
                            <div class="info-box bg-info">
                                <span class="info-box-icon"><i class="fas fa-users"></i></span>
                                <div class="info-box-content">
                                    <span class="info-box-text">Total utilisateurs</span>
                                    <span class="info-box-number">{{ $userStats['total'] }}</span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4 col-sm-6 col-12">
                            <div class="info-box bg-success">
                                <span class="info-box-icon"><i class="fas fa-user-tag"></i></span>
                                <div class="info-box-content">
                                    <span class="info-box-text">Ambassadeurs</span>
                                    <span class="info-box-number">{{ $userStats['influencers'] }}</span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4 col-sm-6 col-12">
                            <div class="info-box bg-warning">
                                <span class="info-box-icon"><i class="fas fa-user-tie"></i></span>
                                <div class="info-box-content">
                                    <span class="info-box-text">Superviseurs</span>
                                    <span class="info-box-number">{{ $userStats['supervisors'] }}</span>
                                </div>
                            </div>
                        </div>
                        <!--<div class="col-md-3 col-sm-6 col-12">
                            <div class="info-box bg-danger">
                                <span class="info-box-icon"><i class="fas fa-user-shield"></i></span>
                                <div class="info-box-content">
                                    <span class="info-box-text">Administrateurs</span>
                                    <span class="info-box-number">{{ $userStats['admins'] }}</span>
                                </div>
                            </div>
                        </div>-->
                    </div>
                    <div class="row mt-4">
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-header">
                                    <h3 class="card-title">Statut des utilisateurs</h3>
                                </div>
                                <div class="card-body">
                                    <canvas id="userStatusChart" style="min-height: 250px; height: 250px; max-height: 250px; max-width: 100%;"></canvas>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-header">
                                    <h3 class="card-title">Activité récente</h3>
                                </div>
                                <div class="card-body">
                                    <p><strong>Utilisateurs actifs (30 derniers jours):</strong> {{ $userStats['recentlyActive'] }}</p>
                                    <div class="progress">
                                        <div class="progress-bar bg-success" role="progressbar" 
                                            style="width: {{ $userStats['total'] > 0 ? ($userStats['recentlyActive'] / $userStats['total']) * 100 : 0 }}%" 
                                            aria-valuenow="{{ $userStats['total'] > 0 ? ($userStats['recentlyActive'] / $userStats['total']) * 100 : 0 }}" 
                                            aria-valuemin="0" 
                                            aria-valuemax="100">
                                            {{ $userStats['total'] > 0 ? round(($userStats['recentlyActive'] / $userStats['total']) * 100) : 0 }}%
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Statistiques des campagnes -->
    <div class="row">
        <div class="col-md-12">
            <div class="card card-success">
                <div class="card-header">
                    <h3 class="card-title">Statistiques des campagnes</h3>
                    <div class="card-tools">
                        <button type="button" class="btn btn-tool" data-card-widget="collapse">
                            <i class="fas fa-minus"></i>
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-3 col-sm-6 col-12">
                            <div class="info-box bg-info">
                                <span class="info-box-icon"><i class="fas fa-bullhorn"></i></span>
                                <div class="info-box-content">
                                    <span class="info-box-text">Total campagnes</span>
                                    <span class="info-box-number">{{ $campaignStats['total'] }}</span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3 col-sm-6 col-12">
                            <div class="info-box bg-success">
                                <span class="info-box-icon"><i class="fas fa-play"></i></span>
                                <div class="info-box-content">
                                    <span class="info-box-text">Campagnes actives</span>
                                    <span class="info-box-number">{{ $campaignStats['active'] }}</span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3 col-sm-6 col-12">
                            <div class="info-box bg-warning">
                                <span class="info-box-icon"><i class="fas fa-edit"></i></span>
                                <div class="info-box-content">
                                    <span class="info-box-text">Campagnes en brouillon</span>
                                    <span class="info-box-number">{{ $campaignStats['draft'] }}</span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3 col-sm-6 col-12">
                            <div class="info-box bg-danger">
                                <span class="info-box-icon"><i class="fas fa-check-circle"></i></span>
                                <div class="info-box-content">
                                    <span class="info-box-text">Campagnes terminées</span>
                                    <span class="info-box-number">{{ $campaignStats['completed'] }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="row mt-4">
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-header">
                                    <h3 class="card-title">Campagnes créées par mois ({{ date('Y') }})</h3>
                                </div>
                                <div class="card-body">
                                    <canvas id="campaignMonthlyChart" style="min-height: 250px; height: 250px; max-height: 250px; max-width: 100%;"></canvas>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-header">
                                    <h3 class="card-title">Statuts des campagnes</h3>
                                </div>
                                <div class="card-body">
                                    <canvas id="campaignStatusChart" style="min-height: 250px; height: 250px; max-height: 250px; max-width: 100%;"></canvas>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Statistiques des contenus -->
    <div class="row">
        <div class="col-md-12">
            <div class="card card-warning">
                <div class="card-header">
                    <h3 class="card-title">Statistiques des contenus</h3>
                    <div class="card-tools">
                        <button type="button" class="btn btn-tool" data-card-widget="collapse">
                            <i class="fas fa-minus"></i>
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4 col-sm-6 col-12">
                            <div class="info-box bg-info">
                                <span class="info-box-icon"><i class="fas fa-file-alt"></i></span>
                                <div class="info-box-content">
                                    <span class="info-box-text">Total contenus</span>
                                    <span class="info-box-number">{{ $contentStats['total'] }}</span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4 col-sm-6 col-12">
                            <div class="info-box bg-success">
                                <span class="info-box-icon"><i class="fas fa-check"></i></span>
                                <div class="info-box-content">
                                    <span class="info-box-text">Contenus approuvés</span>
                                    <span class="info-box-number">{{ $contentStats['approved'] }}</span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4 col-sm-6 col-12">
                            <div class="info-box bg-warning">
                                <span class="info-box-icon"><i class="fas fa-pencil-alt"></i></span>
                                <div class="info-box-content">
                                    <span class="info-box-text">Contenus en brouillon</span>
                                    <span class="info-box-number">{{ $contentStats['draft'] }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="row mt-4">
                        <div class="col-md-12">
                            <div class="card">
                                <div class="card-header">
                                    <h3 class="card-title">Répartition par type de contenu</h3>
                                </div>
                                <div class="card-body">
                                    <canvas id="contentTypeChart" style="min-height: 250px; height: 250px; max-height: 250px; max-width: 100%;"></canvas>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Statistiques des missions -->
    <div class="row">
        <div class="col-md-12">
            <div class="card card-info">
                <div class="card-header">
                    <h3 class="card-title">Statistiques des missions</h3>
                    <div class="card-tools">
                        <button type="button" class="btn btn-tool" data-card-widget="collapse">
                            <i class="fas fa-minus"></i>
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-3 col-sm-6 col-12">
                            <div class="info-box bg-info">
                                <span class="info-box-icon"><i class="fas fa-tasks"></i></span>
                                <div class="info-box-content">
                                    <span class="info-box-text">Total missions</span>
                                    <span class="info-box-number">{{ $assignmentStats['total'] }}</span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3 col-sm-6 col-12">
                            <div class="info-box bg-success">
                                <span class="info-box-icon"><i class="fas fa-check-double"></i></span>
                                <div class="info-box-content">
                                    <span class="info-box-text">Missions acceptées</span>
                                    <span class="info-box-number">{{ $assignmentStats['accepted'] }}</span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3 col-sm-6 col-12">
                            <div class="info-box bg-warning">
                                <span class="info-box-icon"><i class="fas fa-clock"></i></span>
                                <div class="info-box-content">
                                    <span class="info-box-text">Missions en attente</span>
                                    <span class="info-box-number">{{ $assignmentStats['pending'] }}</span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3 col-sm-6 col-12">
                            <div class="info-box bg-danger">
                                <span class="info-box-icon"><i class="fas fa-times"></i></span>
                                <div class="info-box-content">
                                    <span class="info-box-text">Missions rejetées</span>
                                    <span class="info-box-number">{{ $assignmentStats['rejected'] }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="row mt-4">
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-header">
                                    <h3 class="card-title">Missions créées par mois ({{ date('Y') }})</h3>
                                </div>
                                <div class="card-body">
                                    <canvas id="assignmentMonthlyChart" style="min-height: 250px; height: 250px; max-height: 250px; max-width: 100%;"></canvas>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-header">
                                    <h3 class="card-title">Statuts des missions</h3>
                                </div>
                                <div class="card-body">
                                    <canvas id="assignmentStatusChart" style="min-height: 250px; height: 250px; max-height: 250px; max-width: 100%;"></canvas>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Statistiques des publications -->
    <div class="row">
        <div class="col-md-12">
            <div class="card card-danger">
                <div class="card-header">
                    <h3 class="card-title">Statistiques des publications</h3>
                    <div class="card-tools">
                        <button type="button" class="btn btn-tool" data-card-widget="collapse">
                            <i class="fas fa-minus"></i>
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    <!-- Filtres pour les statistiques des publications -->
                    <form method="GET" action="{{ route('admin.statistics') }}" class="mb-4">
                        <div class="row">
                            <div class="col-md-2">
                                <div class="form-group">
                                    <label for="publication_date_from">Date début</label>
                                    <input type="date" class="form-control form-control-sm" id="publication_date_from" name="publication_date_from" value="{{ $publicationFilters['date_from'] ?? '' }}">
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="form-group">
                                    <label for="publication_date_to">Date fin</label>
                                    <input type="date" class="form-control form-control-sm" id="publication_date_to" name="publication_date_to" value="{{ $publicationFilters['date_to'] ?? '' }}">
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="publication_campaign_id">Campagne</label>
                                    <select class="form-control form-control-sm" id="publication_campaign_id" name="publication_campaign_id">
                                        <option value="">Toutes les campagnes</option>
                                        @foreach($campaigns as $campaign)
                                            <option value="{{ $campaign->id }}" {{ ($publicationFilters['campaign_id'] ?? '') == $campaign->id ? 'selected' : '' }}>{{ $campaign->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="publication_supervisor_id">Superviseur</label>
                                    <select class="form-control form-control-sm" id="publication_supervisor_id" name="publication_supervisor_id">
                                        <option value="">Tous les superviseurs</option>
                                        @foreach($supervisors as $supervisor)
                                            <option value="{{ $supervisor->id }}" {{ ($publicationFilters['supervisor_id'] ?? '') == $supervisor->id ? 'selected' : '' }}>{{ $supervisor->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="form-group">
                                    <label>&nbsp;</label>
                                    <div>
                                        <button type="submit" class="btn btn-danger btn-sm">
                                            <i class="fas fa-filter"></i> Filtrer
                                        </button>
                                        <a href="{{ route('admin.statistics.export-publications', request()->query()) }}" class="btn btn-success btn-sm" title="Exporter CSV">
                                            <i class="fas fa-download"></i>
                                        </a>
                                        <a href="{{ route('admin.statistics') }}" class="btn btn-secondary btn-sm">
                                            <i class="fas fa-times"></i>
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </form>
                    
                    <div class="row">
                        <div class="col-md-3 col-sm-6 col-12">
                            <div class="info-box bg-info">
                                <span class="info-box-icon"><i class="fas fa-share-alt"></i></span>
                                <div class="info-box-content">
                                    <span class="info-box-text">Total publications</span>
                                    <span class="info-box-number">{{ $publicationStats['total'] }}</span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3 col-sm-6 col-12">
                            <div class="info-box bg-success">
                                <span class="info-box-icon"><i class="fas fa-check-circle"></i></span>
                                <div class="info-box-content">
                                    <span class="info-box-text">Publications vérifiées</span>
                                    <span class="info-box-number">{{ $publicationStats['verified'] }}</span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3 col-sm-6 col-12">
                            <div class="info-box bg-warning">
                                <span class="info-box-icon"><i class="fas fa-clock"></i></span>
                                <div class="info-box-content">
                                    <span class="info-box-text">Publications en attente</span>
                                    <span class="info-box-number">{{ $publicationStats['pending'] }}</span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3 col-sm-6 col-12">
                            <div class="info-box bg-danger">
                                <span class="info-box-icon"><i class="fas fa-times-circle"></i></span>
                                <div class="info-box-content">
                                    <span class="info-box-text">Publications rejetées</span>
                                    <span class="info-box-number">{{ $publicationStats['rejected'] }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="row mt-4">
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-header">
                                    <h3 class="card-title">Publications par plateforme</h3>
                                </div>
                                <div class="card-body">
                                    <canvas id="publicationPlatformChart" style="min-height: 250px; height: 250px; max-height: 250px; max-width: 100%;"></canvas>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-header">
                                    <h3 class="card-title">Publications par mois ({{ date('Y') }})</h3>
                                </div>
                                <div class="card-body">
                                    <canvas id="publicationMonthlyChart" style="min-height: 250px; height: 250px; max-height: 250px; max-width: 100%;"></canvas>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Top influenceurs et superviseurs -->
    <div class="row">
        <div class="col-md-6">
            <div class="card card-primary">
                <div class="card-header">
                    <h3 class="card-title">Top {{ $influencerFilters['limit'] }} Ambassadeurs (par engagement)</h3>
                    <div class="card-tools">
                        <button type="button" class="btn btn-tool" data-card-widget="collapse">
                            <i class="fas fa-minus"></i>
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    <!-- Filtres pour Top Ambassadeurs -->
                    <form method="GET" action="{{ route('admin.statistics') }}" class="mb-3">
                        <div class="row">
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="influencer_limit">Top</label>
                                    <select class="form-control form-control-sm" id="influencer_limit" name="influencer_limit">
                                        <option value="10" {{ ($influencerFilters['limit'] ?? 10) == 10 ? 'selected' : '' }}>10</option>
                                        <option value="20" {{ ($influencerFilters['limit'] ?? 10) == 20 ? 'selected' : '' }}>20</option>
                                        <option value="50" {{ ($influencerFilters['limit'] ?? 10) == 50 ? 'selected' : '' }}>50</option>
                                        <option value="100" {{ ($influencerFilters['limit'] ?? 10) == 100 ? 'selected' : '' }}>100</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="influencer_date_from">Du</label>
                                    <input type="date" class="form-control form-control-sm" id="influencer_date_from" name="influencer_date_from" value="{{ $influencerFilters['date_from'] ?? '' }}">
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="influencer_date_to">Au</label>
                                    <input type="date" class="form-control form-control-sm" id="influencer_date_to" name="influencer_date_to" value="{{ $influencerFilters['date_to'] ?? '' }}">
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label>&nbsp;</label>
                                    <div>
                                        <button type="submit" class="btn btn-primary btn-sm">
                                            <i class="fas fa-filter"></i>
                                        </button>
                                        <a href="{{ route('admin.statistics.export-top-influencers', request()->query()) }}" class="btn btn-success btn-sm" title="Exporter CSV">
                                            <i class="fas fa-download"></i>
                                        </a>
                                        <a href="{{ route('admin.statistics') }}" class="btn btn-secondary btn-sm">
                                            <i class="fas fa-times"></i>
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </form>
                    
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>Nom</th>
                                <th>Publications</th>
                                <th>Engagement</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($topInfluencers as $influencer)
                            <tr>
                                <td>{{ $influencer->name }}</td>
                                <td>{{ $influencer->publications_count }}</td>
                                <td>{{ number_format($influencer->total_engagement) }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card card-warning">
                <div class="card-header">
                    <h3 class="card-title">Top {{ $supervisorFilters['limit'] }} superviseurs (par engagement)</h3>
                    <div class="card-tools">
                        <button type="button" class="btn btn-tool" data-card-widget="collapse">
                            <i class="fas fa-minus"></i>
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    <!-- Filtres pour Top Superviseurs -->
                    <form method="GET" action="{{ route('admin.statistics') }}" class="mb-3">
                        <div class="row">
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="supervisor_limit">Top</label>
                                    <select class="form-control form-control-sm" id="supervisor_limit" name="supervisor_limit">
                                        <option value="10" {{ ($supervisorFilters['limit'] ?? 10) == 10 ? 'selected' : '' }}>10</option>
                                        <option value="20" {{ ($supervisorFilters['limit'] ?? 10) == 20 ? 'selected' : '' }}>20</option>
                                        <option value="50" {{ ($supervisorFilters['limit'] ?? 10) == 50 ? 'selected' : '' }}>50</option>
                                        <option value="100" {{ ($supervisorFilters['limit'] ?? 10) == 100 ? 'selected' : '' }}>100</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="supervisor_date_from">Du</label>
                                    <input type="date" class="form-control form-control-sm" id="supervisor_date_from" name="supervisor_date_from" value="{{ $supervisorFilters['date_from'] ?? '' }}">
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="supervisor_date_to">Au</label>
                                    <input type="date" class="form-control form-control-sm" id="supervisor_date_to" name="supervisor_date_to" value="{{ $supervisorFilters['date_to'] ?? '' }}">
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label>&nbsp;</label>
                                    <div>
                                        <button type="submit" class="btn btn-warning btn-sm">
                                            <i class="fas fa-filter"></i>
                                        </button>
                                        <a href="{{ route('admin.statistics.export-top-supervisors', request()->query()) }}" class="btn btn-success btn-sm" title="Exporter CSV">
                                            <i class="fas fa-download"></i>
                                        </a>
                                        <a href="{{ route('admin.statistics') }}" class="btn btn-secondary btn-sm">
                                            <i class="fas fa-times"></i>
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </form>
                    
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>Nom</th>
                                <th>Missions supervisées</th>
                                <th>Engagement</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($topSupervisors as $supervisor)
                            <tr>
                                <td>{{ $supervisor->name }}</td>
                                <td>{{ $supervisor->supervisor_assignments_count }}</td>
                                <td>{{ number_format($supervisor->total_engagement) }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Top campagnes -->
    <div class="row">
        <div class="col-md-12">
            <div class="card card-success">
                <div class="card-header">
                    <h3 class="card-title">Top 10 campagnes</h3>
                    <div class="card-tools">
                        <button type="button" class="btn btn-tool" data-card-widget="collapse">
                            <i class="fas fa-minus"></i>
                        </button>
                    </div>
                </div>
                <div class="card-body p-0">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>Titre</th>
                                <th>Publications</th>
                                <th>Statut</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($topCampaigns as $campaign)
                            <tr>
                                <td>{{ $campaign->name }}</td>
                                <td>{{ $campaign->publications_count }}</td>
                                <td>
                                    @if($campaign->status == 'active')
                                        <span class="badge badge-success">Active</span>
                                    @elseif($campaign->status == 'draft')
                                        <span class="badge badge-warning">Brouillon</span>
                                    @elseif($campaign->status == 'completed')
                                        <span class="badge badge-info">Terminée</span>
                                    @elseif($campaign->status == 'archived')
                                        <span class="badge badge-secondary">Archivée</span>
                                    @endif
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<!-- Définition des données PHP pour JavaScript -->
<script type="text/javascript">
    var userStats = <?php echo json_encode($userStats); ?>;
    var campaignStats = <?php echo json_encode($campaignStats); ?>;
    var contentStats = <?php echo json_encode($contentStats); ?>;
    var assignmentStats = <?php echo json_encode($assignmentStats); ?>;
    var publicationStats = <?php echo json_encode($publicationStats); ?>;
    var noteStats = <?php echo json_encode($noteStats); ?>;
    var engagementStats = <?php echo json_encode($engagementStats); ?>;
</script>

<!-- Chargement de Chart.js et initialisation des graphiques -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script type="text/javascript">
$(function() {
    // Construction de l'objet de données pour les graphiques
    var statsData = {
        users: {
            active: userStats.active,
            inactive: userStats.inactive,
            total: userStats.total,
            recentlyActive: userStats.recentlyActive
        },
        campaigns: {
            byMonth: campaignStats.byMonth,
            byStatus: {
                active: campaignStats.active,
                draft: campaignStats.draft,
                completed: campaignStats.completed,
                archived: campaignStats.archived
            }
        },
        contents: {
            byType: contentStats.byType
        },
        assignments: {
            byMonth: assignmentStats.byMonth,
            byStatus: {
                pending: assignmentStats.pending,
                inProgress: assignmentStats.in_progress,
                completed: assignmentStats.completed,
                rejected: assignmentStats.rejected
            }
        },
        publications: {
            byMonth: publicationStats.byMonth,
            byPlatform: publicationStats.byPlatform
        }
    };

    // Fonction pour convertir le numéro du mois en nom
    function getMonthName(monthNumber) {
        const months = [
            'Janvier', 'Février', 'Mars', 'Avril', 'Mai', 'Juin',
            'Juillet', 'Août', 'Septembre', 'Octobre', 'Novembre', 'Décembre'
        ];
        return months[monthNumber - 1] || 'Inconnu';
    }
    // Fonction pour générer des couleurs aléatoires
    function getRandomColor() {
        var letters = '0123456789ABCDEF';
        var color = '#';
        for (var i = 0; i < 6; i++) {
            color += letters[Math.floor(Math.random() * 16)];
        }
        return color;
    }

    // Fonction pour générer un tableau de couleurs
    function generateColors(count) {
        var colors = [];
        for (var i = 0; i < count; i++) {
            colors.push(getRandomColor());
        }
        return colors;
    }

    // Graphique des statuts utilisateurs
    const userCtx = document.getElementById('userStatusChart').getContext('2d');
    const userChart = new Chart(userCtx, {
        type: 'pie',
        data: {
            labels: ['Actifs', 'Inactifs'],
            datasets: [{
                data: [statsData.users.active, statsData.users.inactive],
                backgroundColor: ['#28a745', '#dc3545']
            }]
        },
        options: {
            responsive: true,
            plugins: {
                title: {
                    display: true,
                    text: 'Répartition des utilisateurs'
                }
            }
        }
    });

    // Graphique des campagnes par mois
    const campaignMonthlyCtx = document.getElementById('campaignMonthlyChart').getContext('2d');
    const campaignMonthlyLabels = [];
    const campaignMonthlyData = [];
    
    // Préparation des données pour le graphique
    Object.entries(statsData.campaigns.byMonth).forEach(([month, count]) => {
        campaignMonthlyLabels.push(getMonthName(parseInt(month)));
        campaignMonthlyData.push(count);
    });
    
    const campaignMonthlyChart = new Chart(campaignMonthlyCtx, {
        type: 'bar',
        data: {
            labels: campaignMonthlyLabels,
            datasets: [{
                label: 'Campagnes créées',
                data: campaignMonthlyData,
                backgroundColor: '#00c0ef'
            }]
        },
        options: {
            responsive: true,
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        precision: 0
                    }
                }
            },
            plugins: {
                title: {
                    display: true,
                    text: 'Campagnes créées par mois'
                }
            }
        }
    });

    // Graphique des statuts de campagnes
    const campaignStatusCtx = document.getElementById('campaignStatusChart').getContext('2d');
    const campaignStatusChart = new Chart(campaignStatusCtx, {
        type: 'pie',
        data: {
            labels: ['Actives', 'Brouillons', 'Terminées', 'Archivées'],
            datasets: [{
                data: [
                    statsData.campaigns.byStatus.active, 
                    statsData.campaigns.byStatus.draft, 
                    statsData.campaigns.byStatus.completed, 
                    statsData.campaigns.byStatus.archived
                ],
                backgroundColor: ['#28a745', '#ffc107', '#17a2b8', '#6c757d']
            }]
        },
        options: {
            responsive: true,
            plugins: {
                title: {
                    display: true,
                    text: 'Statuts des campagnes'
                }
            }
        }
    });

    // Graphique des types de contenu
    const contentTypeCtx = document.getElementById('contentTypeChart').getContext('2d');
    const contentTypeLabels = [];
    const contentTypeData = [];
    
    // Préparation des données pour le graphique
    Object.entries(statsData.contents.byType).forEach(([type, count]) => {
        contentTypeLabels.push(type);
        contentTypeData.push(count);
    });
    
    const contentTypeChart = new Chart(contentTypeCtx, {
        type: 'bar',
        data: {
            labels: contentTypeLabels,
            datasets: [{
                label: 'Contenus par type',
                data: contentTypeData,
                backgroundColor: '#6f42c1'
            }]
        },
        options: {
            responsive: true,
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        precision: 0
                    }
                }
            },
            plugins: {
                title: {
                    display: true,
                    text: 'Répartition par type de contenu'
                }
            }
        }
    });

    // Graphique des assignations par mois
    const assignmentMonthlyCtx = document.getElementById('assignmentMonthlyChart').getContext('2d');
    const assignmentMonthlyLabels = [];
    const assignmentMonthlyData = [];
    
    // Préparation des données pour le graphique
    Object.entries(statsData.assignments.byMonth).forEach(([month, count]) => {
        assignmentMonthlyLabels.push(getMonthName(parseInt(month)));
        assignmentMonthlyData.push(count);
    });
    
    const assignmentMonthlyChart = new Chart(assignmentMonthlyCtx, {
        type: 'line',
        data: {
            labels: assignmentMonthlyLabels,
            datasets: [{
                label: 'Assignations créées',
                data: assignmentMonthlyData,
                borderColor: '#fd7e14',
                backgroundColor: 'rgba(253, 126, 20, 0.1)',
                fill: true,
                tension: 0.4
            }]
        },
        options: {
            responsive: true,
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        precision: 0
                    }
                }
            },
            plugins: {
                title: {
                    display: true,
                    text: 'Assignations créées par mois'
                }
            }
        }
    });

    // Graphique des statuts d'assignation
    const assignmentStatusCtx = document.getElementById('assignmentStatusChart').getContext('2d');
    const assignmentStatusChart = new Chart(assignmentStatusCtx, {
        type: 'doughnut',
        data: {
            labels: ['En attente', 'En cours', 'Terminées', 'Rejetées'],
            datasets: [{
                data: [
                    statsData.assignments.byStatus.pending, 
                    statsData.assignments.byStatus.inProgress, 
                    statsData.assignments.byStatus.completed, 
                    statsData.assignments.byStatus.rejected
                ],
                backgroundColor: ['#ffc107', '#17a2b8', '#28a745', '#dc3545']
            }]
        },
        options: {
            responsive: true,
            plugins: {
                title: {
                    display: true,
                    text: 'Statuts des assignations'
                }
            }
        }
    });

    // Graphique des publications par plateforme
    const publicationPlatformCtx = document.getElementById('publicationPlatformChart').getContext('2d');
    const publicationPlatformLabels = [];
    const publicationPlatformData = [];
    
    // Préparation des données pour le graphique
    Object.entries(statsData.publications.byPlatform).forEach(([platform, count]) => {
        publicationPlatformLabels.push(platform);
        publicationPlatformData.push(count);
    });
    
    const publicationPlatformChart = new Chart(publicationPlatformCtx, {
        type: 'pie',
        data: {
            labels: publicationPlatformLabels,
            datasets: [{
                data: publicationPlatformData,
                backgroundColor: generateColors(publicationPlatformLabels.length)
            }]
        },
        options: {
            responsive: true,
            plugins: {
                title: {
                    display: true,
                    text: 'Répartition des publications par plateforme'
                }
            }
        }
    });

    // Graphique des publications par mois
    const publicationMonthlyCtx = document.getElementById('publicationMonthlyChart').getContext('2d');
    const publicationMonthlyLabels = [];
    const publicationMonthlyData = [];
    
    // Préparation des données pour le graphique
    Object.entries(statsData.publications.byMonth).forEach(([month, count]) => {
        publicationMonthlyLabels.push(getMonthName(parseInt(month)));
        publicationMonthlyData.push(count);
    });
    
    const publicationMonthlyChart = new Chart(publicationMonthlyCtx, {
        type: 'bar',
        data: {
            labels: publicationMonthlyLabels,
            datasets: [{
                label: 'Publications',
                data: publicationMonthlyData,
                backgroundColor: '#dd4b39'
            }]
        },
        options: {
            responsive: true,
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        precision: 0
                    }
                }
            },
            plugins: {
                title: {
                    display: true,
                    text: 'Publications par mois'
                }
            }
        }
    });

    // Graphique d'évolution de l'engagement par mois (par plateforme)
    const engagementMonthlyCtx = document.getElementById('engagementMonthlyChart').getContext('2d');
    const engagementMonthlyLabels = [];
    
    // Couleurs pour les plateformes
    const platformColors = {
        'facebook': { border: '#1877F2', bg: 'rgba(24, 119, 242, 0.1)' },
        'instagram': { border: '#E4405F', bg: 'rgba(228, 64, 95, 0.1)' },
        'twitter': { border: '#1DA1F2', bg: 'rgba(29, 161, 242, 0.1)' },
        'x': { border: '#000000', bg: 'rgba(0, 0, 0, 0.1)' },
        'tiktok': { border: '#000000', bg: 'rgba(0, 0, 0, 0.1)' },
        'youtube': { border: '#FF0000', bg: 'rgba(255, 0, 0, 0.1)' },
        'linkedin': { border: '#0A66C2', bg: 'rgba(10, 102, 194, 0.1)' }
    };
    
    // Préparer les labels des mois
    Object.keys(engagementStats.byMonth).forEach((month) => {
        engagementMonthlyLabels.push(getMonthName(parseInt(month)));
    });
    
    // Préparer les datasets par plateforme (total engagement par mois)
    const platformDatasets = [];
    Object.entries(engagementStats.byPlatform).forEach(([platform, data]) => {
        const totalEngagement = (data.likes || 0) + (data.comments || 0) + (data.shares || 0) + (data.views || 0);
        const colors = platformColors[platform.toLowerCase()] || { border: '#6c757d', bg: 'rgba(108, 117, 125, 0.1)' };
        platformDatasets.push({
            label: platform.charAt(0).toUpperCase() + platform.slice(1),
            data: engagementMonthlyLabels.map(() => totalEngagement / engagementMonthlyLabels.length), // Répartition uniforme
            borderColor: colors.border,
            backgroundColor: colors.bg,
            fill: true,
            tension: 0.4
        });
    });
    
    const engagementMonthlyChart = new Chart(engagementMonthlyCtx, {
        type: 'line',
        data: {
            labels: engagementMonthlyLabels,
            datasets: platformDatasets
        },
        options: {
            responsive: true,
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        precision: 0
                    }
                }
            },
            plugins: {
                title: {
                    display: true,
                    text: 'Évolution de l\'engagement par plateforme'
                }
            }
        }
    });

    // Graphique d'engagement par métrique (likes, commentaires, partages, vues)
    const engagementPlatformCtx = document.getElementById('engagementPlatformChart').getContext('2d');
    const metricLabels = ['Likes', 'Commentaires', 'Partages', 'Vues'];
    
    // Préparer les datasets par plateforme
    const metricDatasets = [];
    const metricPlatformColors = {
        'facebook': '#1877F2',
        'instagram': '#E4405F',
        'twitter': '#1DA1F2',
        'x': '#000000',
        'tiktok': '#69C9D0',
        'youtube': '#FF0000',
        'linkedin': '#0A66C2'
    };
    
    Object.entries(engagementStats.byPlatform).forEach(([platform, data]) => {
        const color = metricPlatformColors[platform.toLowerCase()] || '#6c757d';
        metricDatasets.push({
            label: platform.charAt(0).toUpperCase() + platform.slice(1),
            data: [data.likes || 0, data.comments || 0, data.shares || 0, data.views || 0],
            backgroundColor: color
        });
    });
    
    const engagementPlatformChart = new Chart(engagementPlatformCtx, {
        type: 'bar',
        data: {
            labels: metricLabels,
            datasets: metricDatasets
        },
        options: {
            responsive: true,
            scales: {
                x: {
                    stacked: false
                },
                y: {
                    stacked: false,
                    beginAtZero: true,
                    ticks: {
                        precision: 0
                    }
                }
            },
            plugins: {
                title: {
                    display: true,
                    text: 'Engagement par métrique'
                }
            }
        }
    });
});
</script>
@endpush
