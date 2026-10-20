@extends('layouts.adminlte')

@section('title', 'Dashboard')

@section('breadcrumb')
<li class="breadcrumb-item active">Dashboard</li>
@endsection

@section('content')
<!-- Bouton d'export global (visible uniquement pour les admins) -->
@if(in_array(Auth::user()->role, ['admin', 'super_admin']))
<div class="row mb-3">
    <div class="col-12">
        <div class="alert alert-info d-flex align-items-center justify-content-between">
            <div>
                <i class="fas fa-file-excel mr-2"></i>
                <strong>Export complet des données</strong> - Téléchargez toutes les données du site dans un fichier Excel multi-feuilles
            </div>
            <a href="{{ route('admin.export-all') }}" class="btn btn-success">
                <i class="fas fa-download mr-1"></i> Exporter toutes les données
            </a>
        </div>
    </div>
</div>
@endif

<!-- Info boxes -->
<div class="row">
    <div class="col-12 col-sm-6 col-md-4">
        <div class="info-box">
            <span class="info-box-icon bg-success elevation-1"><i class="fas fa-dollar-sign"></i></span>
            <div class="info-box-content">
                <span class="info-box-text">Revenus</span>
                <span class="info-box-number">$ 628</span>
            </div>
        </div>
    </div>
    
    <div class="col-12 col-sm-6 col-md-4">
        <div class="info-box">
            <span class="info-box-icon bg-warning elevation-1"><i class="fas fa-share-alt"></i></span>
            <div class="info-box-content">
                <span class="info-box-text">Partages</span>
                <span class="info-box-number">2434</span>
            </div>
        </div>
    </div>
    
    <div class="col-12 col-sm-6 col-md-4">
        <div class="info-box">
            <span class="info-box-icon bg-info elevation-1"><i class="fas fa-thumbs-up"></i></span>
            <div class="info-box-content">
                <span class="info-box-text">Mentions J'aime</span>
                <span class="info-box-number">1259</span>
            </div>
        </div>
    </div>
</div>
<!-- /.row -->

<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Résultat</h3>
                <div class="card-tools">
                    <span class="badge badge-primary">2023</span>
                </div>
            </div>
            <div class="card-body">
                <div class="chart">
                    <canvas id="barChart" style="min-height: 250px; height: 250px; max-height: 250px; max-width: 100%;"></canvas>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Tendance</h3>
                <div class="card-tools">
                    <button type="button" class="btn btn-tool" data-card-widget="collapse">
                        <i class="fas fa-minus"></i>
                    </button>
                </div>
            </div>
            <div class="card-body">
                <div class="chart">
                    <canvas id="lineChart" style="min-height: 250px; height: 250px; max-height: 250px; max-width: 100%;"></canvas>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-4">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Progression</h3>
                <div class="card-tools">
                    <button type="button" class="btn btn-tool" data-card-widget="collapse">
                        <i class="fas fa-minus"></i>
                    </button>
                </div>
            </div>
            <div class="card-body">
                <div class="text-center mb-4">
                    <canvas id="donutChart" style="height:200px; width:200px;"></canvas>
                    <div class="mt-2 font-weight-bold">45% Complété</div>
                </div>
                <ul class="list-group list-group-unbordered mb-3">
                    <li class="list-group-item">
                        <b>Lorem ipsum</b> <span class="float-right">8/10</span>
                    </li>
                    <li class="list-group-item">
                        <b>Dolor sit amet</b> <span class="float-right">12/15</span>
                    </li>
                    <li class="list-group-item">
                        <b>Consectetur</b> <span class="float-right">5/8</span>
                    </li>
                </ul>
                <a href="#" class="btn btn-warning btn-block">En savoir plus</a>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    $(function () {
        // Bar Chart
        var barChartCanvas = $('#barChart').get(0).getContext('2d');
        var barData = {
            labels: ['Jan', 'Fév', 'Mar', 'Avr', 'Mai', 'Jun', 'Jul'],
            datasets: [
                {
                    label: 'Série 1',
                    backgroundColor: '#007bff',
                    data: [65, 75, 45, 70, 40, 30, 55]
                },
                {
                    label: 'Série 2',
                    backgroundColor: '#ffc107',
                    data: [40, 55, 30, 25, 60, 45, 35]
                }
            ]
        };
        new Chart(barChartCanvas, {
            type: 'bar',
            data: barData,
            options: {
                responsive: true,
                maintainAspectRatio: false
            }
        });

        // Line Chart
        var lineChartCanvas = $('#lineChart').get(0).getContext('2d');
        var lineData = {
            labels: ['Jan', 'Fév', 'Mar', 'Avr', 'Mai', 'Jun', 'Jul'],
            datasets: [
                {
                    label: 'Série 1',
                    borderColor: '#007bff',
                    backgroundColor: 'rgba(0, 123, 255, 0.1)',
                    fill: true,
                    data: [65, 75, 45, 70, 40, 30, 55]
                },
                {
                    label: 'Série 2',
                    borderColor: '#ffc107',
                    backgroundColor: 'rgba(255, 193, 7, 0.1)',
                    fill: true,
                    data: [40, 55, 30, 25, 60, 45, 35]
                }
            ]
        };
        new Chart(lineChartCanvas, {
            type: 'line',
            data: lineData,
            options: {
                responsive: true,
                maintainAspectRatio: false
            }
        });

        // Donut Chart
        var donutChartCanvas = $('#donutChart').get(0).getContext('2d');
        var donutData = {
            labels: ['Complété', 'Restant'],
            datasets: [
                {
                    data: [45, 55],
                    backgroundColor: ['#ffc107', '#e9ecef']
                }
            ]
        };
        new Chart(donutChartCanvas, {
            type: 'doughnut',
            data: donutData,
            options: {
                responsive: true,
                maintainAspectRatio: false,
                cutout: '70%'
            }
        });
    });
</script>
@endpush
