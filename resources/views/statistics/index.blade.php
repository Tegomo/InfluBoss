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
    <div class="row">
        <div class="col-sm-12">
            <div class="btn-group float-right gap-2" role="group">
                <a href="{{ route('admin.statistics.export') }}" class="btn btn-success">
                    <i class="fas fa-file-excel mr-1"></i> Exporter Excel
                </a>
                <a href="{{ route('admin.statistics.export-pdf') }}" class="btn btn-danger">
                    <i class="fas fa-file-pdf mr-1"></i> Exporter PDF
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
                                                <div class="progress-bar bg-primary" role="progressbar" style="width: {{ $engagementStats['total']['likes'] / max(1, max(array_filter($engagementStats['total']))) * 100 }}%" aria-valuenow="{{ $engagementStats['total']['likes'] }}" aria-valuemin="0" aria-valuemax="{{ max(array_filter($engagementStats['total'])) }}">{{ number_format($engagementStats['total']['likes']) }}</div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row mb-3">
                                        <div class="col-2">Commentaires</div>
                                        <div class="col-10">
                                            <div class="progress" style="height: 20px;">
                                                <div class="progress-bar bg-success" role="progressbar" style="width: {{ $engagementStats['total']['comments'] / max(1, max(array_filter($engagementStats['total']))) * 100 }}%" aria-valuenow="{{ $engagementStats['total']['comments'] }}" aria-valuemin="0" aria-valuemax="{{ max(array_filter($engagementStats['total'])) }}">{{ number_format($engagementStats['total']['comments']) }}</div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row mb-3">
                                        <div class="col-2">Partages</div>
                                        <div class="col-10">
                                            <div class="progress" style="height: 20px;">
                                                <div class="progress-bar bg-danger" role="progressbar" style="width: {{ $engagementStats['total']['shares'] / max(1, max(array_filter($engagementStats['total']))) * 100 }}%" aria-valuenow="{{ $engagementStats['total']['shares'] }}" aria-valuemin="0" aria-valuemax="{{ max(array_filter($engagementStats['total'])) }}">{{ number_format($engagementStats['total']['shares']) }}</div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row mb-3">
                                        <div class="col-2">Vues</div>
                                        <div class="col-10">
                                            <div class="progress" style="height: 20px;">
                                                <div class="progress-bar bg-warning" role="progressbar" style="width: {{ $engagementStats['total']['views'] / max(1, max(array_filter($engagementStats['total']))) * 100 }}%" aria-valuenow="{{ $engagementStats['total']['views'] }}" aria-valuemin="0" aria-valuemax="{{ max(array_filter($engagementStats['total'])) }}">{{ number_format($engagementStats['total']['views']) }}</div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-2">Clics</div>
                                        <div class="col-10">
                                            <div class="progress" style="height: 20px;">
                                                <div class="progress-bar bg-info" role="progressbar" style="width: {{ isset($engagementStats['total']['clicks']) ? $engagementStats['total']['clicks'] / max(1, max(array_filter($engagementStats['total']))) * 100 : 0 }}%" aria-valuenow="{{ isset($engagementStats['total']['clicks']) ? $engagementStats['total']['clicks'] : 0 }}" aria-valuemin="0" aria-valuemax="{{ max(array_filter($engagementStats['total'])) }}">{{ number_format(isset($engagementStats['total']['clicks']) ? $engagementStats['total']['clicks'] : 0) }}</div>
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
                                    <h3 class="card-title">Évolution de l'engagement par mois ({{ date('Y') }})</h3>
                                </div>
                                <div class="card-body">
                                    <canvas id="engagementMonthlyChart" style="min-height: 250px; height: 250px; max-height: 250px; max-width: 100%;"></canvas>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-header">
                                    <h3 class="card-title">Engagement par plateforme</h3>
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
                    <h3 class="card-title">Top 10 Ambassadeurs</h3>
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
                                <th>Nom</th>
                                <th>Publications</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($topInfluencers as $influencer)
                            <tr>
                                <td>{{ $influencer->name }}</td>
                                <td>{{ $influencer->publications_count }}</td>
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
                    <h3 class="card-title">Top 10 superviseurs</h3>
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
                                <th>Nom</th>
                                <th>Missions supervisées</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($topSupervisors as $supervisor)
                            <tr>
                                <td>{{ $supervisor->name }}</td>
                                <td>{{ $supervisor->supervisor_assignments_count }}</td>
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

    // Graphique d'évolution de l'engagement par mois
    const engagementMonthlyCtx = document.getElementById('engagementMonthlyChart').getContext('2d');
    const engagementMonthlyLabels = [];
    const likesData = [];
    const commentsData = [];
    const sharesData = [];
    const viewsData = [];
    
    // Préparation des données pour le graphique
    Object.entries(engagementStats.byMonth).forEach(([month, data]) => {
        engagementMonthlyLabels.push(getMonthName(parseInt(month)));
        likesData.push(data.likes);
        commentsData.push(data.comments);
        sharesData.push(data.shares);
        viewsData.push(data.views);
    });
    
    const engagementMonthlyChart = new Chart(engagementMonthlyCtx, {
        type: 'line',
        data: {
            labels: engagementMonthlyLabels,
            datasets: [
                {
                    label: 'Likes',
                    data: likesData,
                    borderColor: '#28a745',
                    backgroundColor: 'rgba(40, 167, 69, 0.1)',
                    fill: true,
                    tension: 0.4
                },
                {
                    label: 'Commentaires',
                    data: commentsData,
                    borderColor: '#007bff',
                    backgroundColor: 'rgba(0, 123, 255, 0.1)',
                    fill: true,
                    tension: 0.4
                },
                {
                    label: 'Partages',
                    data: sharesData,
                    borderColor: '#ffc107',
                    backgroundColor: 'rgba(255, 193, 7, 0.1)',
                    fill: true,
                    tension: 0.4
                },
                {
                    label: 'Vues',
                    data: viewsData,
                    borderColor: '#17a2b8',
                    backgroundColor: 'rgba(23, 162, 184, 0.1)',
                    fill: true,
                    tension: 0.4
                }
            ]
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
                    text: 'Évolution de l\'engagement par mois'
                }
            }
        }
    });

    // Graphique d'engagement par plateforme
    const engagementPlatformCtx = document.getElementById('engagementPlatformChart').getContext('2d');
    const platformLabels = [];
    const platformLikesData = [];
    const platformCommentsData = [];
    const platformSharesData = [];
    const platformViewsData = [];
    
    // Préparation des données pour le graphique
    Object.entries(engagementStats.byPlatform).forEach(([platform, data]) => {
        platformLabels.push(platform);
        platformLikesData.push(data.likes);
        platformCommentsData.push(data.comments);
        platformSharesData.push(data.shares);
        platformViewsData.push(data.views);
    });
    
    const engagementPlatformChart = new Chart(engagementPlatformCtx, {
        type: 'bar',
        data: {
            labels: platformLabels,
            datasets: [
                {
                    label: 'Likes',
                    data: platformLikesData,
                    backgroundColor: '#28a745'
                },
                {
                    label: 'Commentaires',
                    data: platformCommentsData,
                    backgroundColor: '#007bff'
                },
                {
                    label: 'Partages',
                    data: platformSharesData,
                    backgroundColor: '#ffc107'
                },
                {
                    label: 'Vues',
                    data: platformViewsData,
                    backgroundColor: '#17a2b8'
                }
            ]
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
                    text: 'Engagement par plateforme'
                }
            }
        }
    });
});
</script>
@endpush
