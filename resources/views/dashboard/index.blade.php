@extends('layouts.adminlte')

@section('title', 'Dashboard')

@section('breadcrumb')
<li class="breadcrumb-item active">Dashboard</li>
@endsection

@section('content')
<!-- Info boxes -->
<div class="row">
    <div class="col-12 col-sm-6 col-md-3">
        <div class="info-box">
            <span class="info-box-icon bg-info elevation-1"><i class="fas fa-bullhorn"></i></span>
            <div class="info-box-content">
                <span class="info-box-text">Campagnes</span>
                <span class="info-box-number">{{ $stats['campaigns'] }}</span>
            </div>
            <!-- /.info-box-content -->
        </div>
        <!-- /.info-box -->
    </div>
    <!-- /.col -->
    <div class="col-12 col-sm-6 col-md-3">
        <div class="info-box mb-3">
            <span class="info-box-icon bg-success elevation-1"><i class="fas fa-file-alt"></i></span>
            <div class="info-box-content">
                <span class="info-box-text">Contenus</span>
                <span class="info-box-number">{{ $stats['contents'] }}</span>
            </div>
            <!-- /.info-box-content -->
        </div>
        <!-- /.info-box -->
    </div>
    <!-- /.col -->

    <!-- fix for small devices only -->
    <div class="clearfix hidden-md-up"></div>

    <div class="col-12 col-sm-6 col-md-3">
        <div class="info-box mb-3">
            <span class="info-box-icon bg-warning elevation-1"><i class="fas fa-share-alt"></i></span>
            <div class="info-box-content">
                <span class="info-box-text">Publications</span>
                <span class="info-box-number">{{ $stats['publications'] }}</span>
            </div>
            <!-- /.info-box-content -->
        </div>
        <!-- /.info-box -->
    </div>
    <!-- /.col -->
    <div class="col-12 col-sm-6 col-md-3">
        <div class="info-box mb-3">
            <span class="info-box-icon bg-danger elevation-1"><i class="fas fa-users"></i></span>
            <div class="info-box-content">
                <span class="info-box-text">Embaassadeurs</span>
                <span class="info-box-number">{{ $stats['influencers'] }}</span>
            </div>
            <!-- /.info-box-content -->
        </div>
        <!-- /.info-box -->
    </div>
    <!-- /.col -->
</div>
<!-- /.row -->

<!-- Graphiques statistiques -->
<div class="row">
    <!-- Graphique des campagnes par statut -->
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Campagnes par statut</h3>
                <div class="card-tools">
                    <button type="button" class="btn btn-tool" data-card-widget="collapse">
                        <i class="fas fa-minus"></i>
                    </button>
                </div>
            </div>
            <div class="card-body">
                <canvas id="campaignStatusChart" style="min-height: 250px; height: 250px; max-height: 250px; max-width: 100%;"></canvas>
            </div>
        </div>
    </div>
    
    <!-- Graphique des publications mensuelles -->
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Publications mensuelles</h3>
                <div class="card-tools">
                    <button type="button" class="btn btn-tool" data-card-widget="collapse">
                        <i class="fas fa-minus"></i>
                    </button>
                </div>
            </div>
            <div class="card-body">
                <canvas id="monthlyPublicationsChart" style="min-height: 250px; height: 250px; max-height: 250px; max-width: 100%;"></canvas>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <!-- Graphique des interactions -->
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Statistiques d'engagement</h3>
                <div class="card-tools">
                    <button type="button" class="btn btn-tool" data-card-widget="collapse">
                        <i class="fas fa-minus"></i>
                    </button>
                </div>
            </div>
            <div class="card-body">
                <canvas id="interactionStatsChart" style="min-height: 250px; height: 250px; max-height: 250px; max-width: 100%;"></canvas>
            </div>
        </div>
    </div>
    
    <!-- Graphique des plateformes -->
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Publications par plateforme</h3>
                <div class="card-tools">
                    <button type="button" class="btn btn-tool" data-card-widget="collapse">
                        <i class="fas fa-minus"></i>
                    </button>
                </div>
            </div>
            <div class="card-body">
                <canvas id="platformStatsChart" style="min-height: 250px; height: 250px; max-height: 250px; max-width: 100%;"></canvas>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-8">
        <!-- TABLE: LATEST CAMPAIGNS -->
        <div class="card">
            <div class="card-header border-transparent">
                <h3 class="card-title">Campagnes récentes</h3>
                <div class="card-tools">
                    <button type="button" class="btn btn-tool" data-card-widget="collapse">
                        <i class="fas fa-minus"></i>
                    </button>
                </div>
            </div>
            <!-- /.card-header -->
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table m-0">
                        <thead>
                            <tr>
                                <th>Nom</th>
                                <th>Créé par</th>
                                <th>Statut</th>
                                <th>Date</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($recentCampaigns as $campaign)
                            <tr>
                                <td><a href="{{ route('campaigns.show', $campaign) }}">{{ $campaign->name }}</a></td>
                                <td>{{ $campaign->creator->name }}</td>
                                <td>
                                    @php
                                        $badgeClass = 'secondary';
                                        switch($campaign->status ?? '') {
                                            case 'active':
                                                $badgeClass = 'success';
                                                break;
                                            case 'pending':
                                                $badgeClass = 'warning';
                                                break;
                                            case 'completed':
                                                $badgeClass = 'info';
                                                break;
                                            case 'cancelled':
                                                $badgeClass = 'danger';
                                                break;
                                        }
                                    @endphp
                                    <span class="badge badge-{{ $badgeClass }}">{{ ucfirst($campaign->status ?? 'draft') }}</span>
                                </td>
                                <td>{{ $campaign->created_at->format('d/m/Y') }}</td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="4" class="text-center">Aucune campagne récente.</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <!-- /.table-responsive -->
            </div>
            <!-- /.card-body -->
            <div class="card-footer clearfix">
                <a href="{{ route('campaigns.create') }}" class="btn btn-sm btn-info float-left">Nouvelle campagne</a>
                <a href="{{ route('campaigns.index') }}" class="btn btn-sm btn-secondary float-right">Voir toutes les campagnes</a>
            </div>
            <!-- /.card-footer -->
        </div>
        <!-- /.card -->
    </div>
    <!-- /.col -->
    <div class="col-md-4">
        <!-- RECENT PUBLICATIONS -->
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Publications récentes</h3>
                <div class="card-tools">
                    <button type="button" class="btn btn-tool" data-card-widget="collapse">
                        <i class="fas fa-minus"></i>
                    </button>
                </div>
            </div>
            <!-- /.card-header -->
            <div class="card-body p-0">
                <ul class="products-list product-list-in-card pl-2 pr-2">
                    @forelse($recentPublications as $publication)
                    <li class="item">
                        <div class="product-img">
                            @php
                                $icon = 'fas fa-share-alt';
                                switch($publication->platform) {
                                    case 'instagram':
                                        $icon = 'fab fa-instagram';
                                        break;
                                    case 'facebook':
                                        $icon = 'fab fa-facebook';
                                        break;
                                    case 'twitter':
                                        $icon = 'fab fa-twitter';
                                        break;
                                    case 'tiktok':
                                        $icon = 'fab fa-tiktok';
                                        break;
                                }
                            @endphp
                            <i class="{{ $icon }} fa-2x"></i>
                        </div>
                        <div class="product-info">
                            <a href="javascript:void(0)" class="product-title">{{ $publication->content ? $publication->content->title : 'Publication sans contenu' }}
                                @php
                                    $badgeClass = 'secondary';
                                    switch($publication->status ?? '') {
                                        case 'published':
                                            $badgeClass = 'success';
                                            break;
                                        case 'pending':
                                            $badgeClass = 'warning';
                                            break;
                                        case 'rejected':
                                            $badgeClass = 'danger';
                                            break;
                                    }
                                @endphp
                                <span class="badge badge-{{ $badgeClass }} float-right">{{ ucfirst($publication->status ?? 'draft') }}</span>
                            </a>
                            <span class="product-description">
                                Par {{ $publication->influencer->name }} - {{ $publication->published_at ? $publication->published_at->format('d/m/Y') : 'N/A' }}
                            </span>
                        </div>
                    </li>
                    @empty
                    <li class="item">
                        <div class="product-info">
                            <span class="product-description text-center py-3">
                                Aucune publication récente.
                            </span>
                        </div>
                    </li>
                    @endforelse
                </ul>
            </div>
            <!-- /.card-body -->
            <div class="card-footer text-center">
                <a href="{{ route('publications.index') }}" class="uppercase">Voir toutes les publications</a>
            </div>
            <!-- /.card-footer -->
        </div>
        <!-- /.card -->
    </div>
    <!-- /.col -->
</div>
<!-- /.row -->
@endsection

@push('scripts')
<!-- Variables pour les graphiques -->
<script>
    // Définition des données pour les graphiques sous forme de variables JSON séparées
    var dashboardDataJSON = '@json($dashboardData)';
    
    // Conversion de la chaîne JSON en objet JavaScript
    window.dashboardData = JSON.parse(dashboardDataJSON.replace(/&quot;/g, '"'));
</script>
@endpush
