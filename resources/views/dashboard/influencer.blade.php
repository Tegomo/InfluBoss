@extends('layouts.adminlte')

@section('title', 'Tableau de bord - Influenceur')

@section('breadcrumb')
<li class="breadcrumb-item active">Dashboard</li>
@endsection

@section('content')
<div class="row">
    <!-- Assignations -->
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Mes assignations</h3>
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
                                <th>Campagne</th>
                                <th>Contenu</th>
                                <th>Statut</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($assignments as $assignment)
                            <tr>
                                <td>{{ $assignment->campaign->name }}</td>
                                <td>{{ $assignment->content->title }}</td>
                                <td>
                                    @php
                                        $badgeClass = 'secondary';
                                        switch($assignment->status ?? '') {
                                            case 'accepted':
                                                $badgeClass = 'success';
                                                break;
                                            case 'pending':
                                                $badgeClass = 'warning';
                                                break;
                                            case 'rejected':
                                                $badgeClass = 'danger';
                                                break;
                                            case 'completed':
                                                $badgeClass = 'info';
                                                break;
                                        }
                                    @endphp
                                    <span class="badge badge-{{ $badgeClass }}">{{ ucfirst($assignment->status ?? 'pending') }}</span>
                                </td>
                                <td>
                                    <a href="{{ route('assignments.show', $assignment) }}" class="btn btn-xs btn-info">
                                        <i class="fas fa-eye"></i> Voir
                                    </a>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="4" class="text-center">Aucune assignation.</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <!-- /.table-responsive -->
            </div>
            <!-- /.card-body -->
            <div class="card-footer clearfix">
                <a href="#" class="btn btn-sm btn-secondary float-right">Voir toutes les assignations</a>
            </div>
            <!-- /.card-footer -->
        </div>
        <!-- /.card -->
    </div>
    <!-- /.col -->

    <!-- Publications -->
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Mes publications</h3>
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
                                <th>Plateforme</th>
                                <th>Statut</th>
                                <th>Date</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($publications as $publication)
                            <tr>
                                <td>
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
                                    <i class="{{ $icon }}"></i> {{ ucfirst($publication->platform) }}
                                </td>
                                <td>
                                    @php
                                        $badgeClass = 'secondary';
                                        switch($publication->status ?? '') {
                                            case 'verified':
                                            case 'published':
                                                $badgeClass = 'success';
                                                break;
                                            case 'pending':
                                            case 'pending_verification':
                                                $badgeClass = 'warning';
                                                break;
                                            case 'rejected':
                                                $badgeClass = 'danger';
                                                break;
                                        }
                                    @endphp
                                    <span class="badge badge-{{ $badgeClass }}">{{ ucfirst($publication->status ?? 'pending') }}</span>
                                </td>
                                <td>{{ $publication->published_at ? $publication->published_at->format('d/m/Y H:i') : 'N/A' }}</td>
                                <td>
                                    <a href="{{ route('publications.show', $publication) }}" class="btn btn-xs btn-info">
                                        <i class="fas fa-eye"></i> Voir
                                    </a>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="4" class="text-center">Aucune publication.</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <!-- /.table-responsive -->
            </div>
            <!-- /.card-body -->
            <div class="card-footer clearfix">
                <a href="{{ route('publications.index') }}" class="btn btn-sm btn-secondary float-right">Voir toutes les publications</a>
            </div>
            <!-- /.card-footer -->
        </div>
        <!-- /.card -->
    </div>
    <!-- /.col -->
</div>
<!-- /.row -->
@endsection
