@extends('layouts.adminlte')

@section('title', 'Tableau de bord - Superviseur')

@section('breadcrumb')
<li class="breadcrumb-item active">Dashboard</li>
@endsection

@section('content')
<div class="row">
    <!-- Campagnes gérées -->
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Mes campagnes</h3>
                <div class="card-tools">
                    <button type="button" class="btn btn-tool" data-card-widget="collapse">
                        <i class="fas fa-minus"></i>
                    </button>
                </div>
            </div>
            <!-- /.card-header -->
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Nom</th>
                                <th>Statut</th>
                                <th>Créé le</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($campaigns as $campaign)
                            <tr>
                                <td>{{ $campaign->name }}</td>
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
                                <td>
                                    <a href="{{ route('campaigns.show', $campaign) }}" class="btn btn-xs btn-info">
                                        <i class="fas fa-eye"></i> Voir
                                    </a>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="4" class="text-center">Aucune campagne assignée.</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <!-- /.table-responsive -->
            </div>
            <!-- /.card-body -->
            <div class="card-footer clearfix">
                <a href="{{ route('campaigns.index') }}" class="btn btn-sm btn-secondary float-right">Voir toutes les campagnes</a>
            </div>
            <!-- /.card-footer -->
        </div>
        <!-- /.card -->
    </div>
    <!-- /.col -->

    <!-- Influenceurs supervisés -->
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Mes embaassadeurs</h3>
                <div class="card-tools">
                    <button type="button" class="btn btn-tool" data-card-widget="collapse">
                        <i class="fas fa-minus"></i>
                    </button>
                </div>
            </div>
            <!-- /.card-header -->
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Nom</th>
                                <th>Publications</th>
                                <th>Engagement</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($influencers as $influencer)
                            <tr>
                                <td>{{ $influencer->name }}</td>
                                <td>{{ $influencer->stats->total_publications ?? 0 }}</td>
                                <td>{{ $influencer->stats->engagement_rate ?? 0 }}%</td>
                                <td>
                                    <a href="{{ route('users.show', $influencer) }}" class="btn btn-xs btn-info">
                                        <i class="fas fa-eye"></i> Voir
                                    </a>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="4" class="text-center">Aucun influenceur assigné.</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <!-- /.table-responsive -->
            </div>
            <!-- /.card-body -->
            <div class="card-footer clearfix">
                <a href="{{ route('users.index', ['role' => 'influencer']) }}" class="btn btn-sm btn-secondary float-right">Voir tous les Ambassadeurs</a>
            </div>
            <!-- /.card-footer -->
        </div>
        <!-- /.card -->
    </div>
    <!-- /.col -->
</div>
<!-- /.row -->
@endsection
