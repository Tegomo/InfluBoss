<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>InfluBoss | @yield('title')</title>
    
    <!-- Favicon -->
    <link rel="icon" href="{{ asset('image/orange-est-la.png') }}" type="image/png">
    <link rel="shortcut icon" href="{{ asset('image/orange-est-la.png') }}" type="image/png">

    <!-- Google Font: Source Sans Pro -->
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700&display=fallback">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <!-- Theme style -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/admin-lte@3.2.0/dist/css/adminlte.min.css">
    <!-- Select2 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/@ttskch/select2-bootstrap4-theme@1.5.2/dist/select2-bootstrap4.min.css" rel="stylesheet" />
    <!-- Custom CSS -->
    <link href="{{ asset('css/app.css') }}" rel="stylesheet">
</head>
<body class="hold-transition sidebar-mini layout-fixed">
<div class="wrapper">

    <!-- Navbar -->
    <nav class="main-header navbar navbar-expand navbar-white navbar-light">
        <!-- Left navbar links -->
        <ul class="navbar-nav">
            <li class="nav-item">
                <a class="nav-link" data-widget="pushmenu" href="#" role="button"><i class="fas fa-bars"></i></a>
            </li>
        </ul>

        <!-- Right navbar links -->
        <ul class="navbar-nav ml-auto">
            <!-- Notifications Dropdown Menu -->
            @php
                $unreadCount = Auth::user()->unreadNotifications()->count();
                $latestUnread = $unreadCount > 0 ? Auth::user()->unreadNotifications()->latest()->take(5)->get() : collect();
            @endphp
            <li class="nav-item dropdown">
                <a class="nav-link" data-toggle="dropdown" href="#" title="Notifications">
                    <i class="far fa-bell"></i>
                    @if($unreadCount > 0)
                    <span class="badge badge-warning navbar-badge">{{ $unreadCount }}</span>
                    @endif
                </a>
                <div class="dropdown-menu dropdown-menu-lg dropdown-menu-right">
                    <span class="dropdown-item dropdown-header">
                        {{ $unreadCount > 0 ? $unreadCount . ' notification(s) non lue(s)' : 'Aucune nouvelle notification' }}
                    </span>
                    @foreach($latestUnread as $notification)
                        <div class="dropdown-divider"></div>
                        <a href="{{ route('notifications.read', $notification->id) }}" class="dropdown-item" style="white-space: normal;">
                            <i class="{{ $notification->data['icon'] ?? 'fas fa-bell' }} mr-2"></i>
                            {{ $notification->data['message'] ?? 'Notification' }}
                            <span class="float-right text-muted text-sm">{{ $notification->created_at->diffForHumans(null, true) }}</span>
                        </a>
                    @endforeach
                    @if($unreadCount > 0)
                        <div class="dropdown-divider"></div>
                        <form method="POST" action="{{ route('notifications.read-all') }}" class="m-0">
                            @csrf
                            <button type="submit" class="dropdown-item dropdown-footer">Tout marquer comme lu</button>
                        </form>
                    @endif
                </div>
            </li>
            <li class="nav-item">
                <a class="nav-link" data-widget="fullscreen" href="#" role="button">
                    <i class="fas fa-expand-arrows-alt"></i>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="{{ route('logout') }}" onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                    <i class="fas fa-sign-out-alt"></i>
                </a>
                <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">
                    @csrf
                </form>
            </li>
        </ul>
    </nav>
    <!-- /.navbar -->

    <!-- Main Sidebar Container -->
    <aside class="main-sidebar sidebar-dark-primary elevation-4">
        <!-- Brand Logo -->
        <a href="{{ route('dashboard') }}" class="brand-link">
            <span class="brand-text font-weight-light">McCANN InfluBoss </span>
        </a>

        <!-- Sidebar -->
        <div class="sidebar">
            <!-- Sidebar user panel (optional) -->
            <div class="user-panel mt-3 pb-3 mb-3 d-flex">
                <div class="image">
                    <i class="fas fa-user-circle fa-2x text-white"></i>
                </div>
                <div class="info">
                    <a href="#" class="d-block">{{ Auth::user()->name }}</a>
                </div>
            </div>

            <!-- Sidebar Menu -->
            <nav class="mt-2">
                <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu" data-accordion="false">
                    <li class="nav-item">
                        <a href="{{ route('dashboard') }}" class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}">
                            <i class="nav-icon fas fa-tachometer-alt"></i>
                            <p>Dashboard</p>
                        </a>
                    </li>
                    
                    {{-- Menu Utilisateurs visible uniquement pour les admins --}}
                    @if(Auth::user()->role === 'admin' || Auth::user()->role === 'super_admin')
                    <li class="nav-item">
                        <a href="{{ route('users.index') }}" class="nav-link {{ request()->routeIs('users.*') && !request()->query('role') ? 'active' : '' }}">
                            <i class="nav-icon fas fa-users"></i>
                            <p>Utilisateurs</p>
                        </a>
                    </li>
                    @endif
                    
                    {{-- Menu Influenceurs : admins et client (lecture seule) --}}
                    @if(in_array(Auth::user()->role, ['admin', 'super_admin', 'client']))
                    <li class="nav-item">
                        <a href="{{ route('influencers.index') }}" class="nav-link {{ request()->routeIs('influencers.*') ? 'active' : '' }}">
                            <i class="nav-icon fas fa-user-tag"></i>
                            <p>Ambassadeurs</p>
                        </a>
                    </li>
                    @endif
                    
                    {{-- Menu Superviseurs visible uniquement pour les admins --}}
                    @if(Auth::user()->role === 'admin' || Auth::user()->role === 'super_admin')
                    <li class="nav-item">
                        <a href="{{ route('supervisors.index') }}" class="nav-link {{ request()->routeIs('supervisors.*') ? 'active' : '' }}">
                            <i class="nav-icon fas fa-user-shield"></i>
                            <p>Superviseurs</p>
                        </a>
                    </li>
                    @endif
                    
                    @can('viewAny', \App\Models\Contract::class)
                    <li class="nav-item">
                        <a href="{{ route('contracts.index') }}" class="nav-link {{ request()->routeIs('contracts.*') ? 'active' : '' }}">
                            <i class="nav-icon fas fa-file-signature"></i>
                            <p>{{ Auth::user()->role === 'influencer' ? 'Mes contrats' : 'Contrats' }}</p>
                        </a>
                    </li>
                    @endcan

                    @can('viewAny', \App\Models\Payment::class)
                    <li class="nav-item">
                        <a href="{{ route('payments.index') }}" class="nav-link {{ request()->routeIs('payments.*') ? 'active' : '' }}">
                            <i class="nav-icon fas fa-money-bill-wave"></i>
                            <p>{{ Auth::user()->role === 'influencer' ? 'Mes paiements' : 'Paiements' }}</p>
                        </a>
                    </li>
                    @endcan

                    @can('viewAny', \App\Models\Campaign::class)
                    <li class="nav-item">
                        <a href="{{ route('campaigns.index') }}" class="nav-link {{ request()->routeIs('campaigns.*') ? 'active' : '' }}">
                            <i class="nav-icon fas fa-bullhorn"></i>
                            <p>Campagnes</p>
                        </a>
                    </li>
                    @endcan
                    
                    @can('viewAny', \App\Models\Content::class)
                    <li class="nav-item">
                        <a href="{{ route('contents.index') }}" class="nav-link {{ request()->routeIs('contents.*') ? 'active' : '' }}">
                            <i class="nav-icon fas fa-images"></i>
                            <p>Contenus</p>
                        </a>
                    </li>
                    @endcan
                    
                    {{-- Menu Missions visible pour tous les utilisateurs authentifiés --}}
                    <li class="nav-item">
                        <a href="{{ route('assignments.index') }}" class="nav-link {{ request()->routeIs('assignments.*') ? 'active' : '' }}">
                            <i class="nav-icon fas fa-tasks"></i>
                            <p>
                                @if(Auth::user()->role === 'influencer')
                                    Mes Missions
                                @else
                                    Missions
                                @endif
                            </p>
                        </a>
                    </li>
                    
                    {{-- Menu Publications visible pour tous les utilisateurs authentifiés --}}
                    <li class="nav-item">
                        <a href="{{ route('publications.index') }}" class="nav-link {{ request()->routeIs('publications.*') ? 'active' : '' }}">
                            <i class="nav-icon fas fa-newspaper"></i>
                            <p>
                                @if(Auth::user()->role === 'influencer')
                                    Mes Publications
                                @else
                                    Publications
                                @endif
                            </p>
                        </a>
                    </li>
                    
                    {{-- Menu Statistiques : admins et client (lecture seule) --}}
                    @if(in_array(Auth::user()->role, ['admin', 'super_admin', 'client']))
                    <li class="nav-item">
                        <a href="{{ route('admin.statistics') }}" class="nav-link {{ request()->routeIs('admin.statistics') ? 'active' : '' }}">
                            <i class="nav-icon fas fa-chart-bar"></i>
                            <p>Statistiques</p>
                        </a>
                    </li>
                    @endif
                </ul>
            </nav>
            <!-- /.sidebar-menu -->
        </div>
        <!-- /.sidebar -->
    </aside>

    <!-- Content Wrapper. Contains page content -->
    <div class="content-wrapper">
        <!-- Content Header (Page header) -->
        <div class="content-header">
            <div class="container-fluid">
                <div class="row mb-2">
                    <div class="col-sm-6">
                        <h1 class="m-0">@yield('title')</h1>
                    </div>
                    <div class="col-sm-6">
                        <ol class="breadcrumb float-sm-right">
                            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Accueil</a></li>
                            @yield('breadcrumb')
                        </ol>
                    </div>
                </div>
            </div>
        </div>
        <!-- /.content-header -->

        <!-- Main content -->
        <section class="content">
            <div class="container-fluid">
                @yield('content')
            </div>
        </section>
        <!-- /.content -->
    </div>
    <!-- /.content-wrapper -->

    <footer class="main-footer">
        <strong>Copyright &copy; {{ date('Y') }} <a href="#">McCANN</a>.</strong>
        Tous droits réservés.
        <div class="float-right d-none d-sm-inline-block">
            <b>Version</b> 1.0.0
        </div>
    </footer>
</div>
<!-- ./wrapper -->

<!-- jQuery -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<!-- Bootstrap 4 -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.0/dist/js/bootstrap.bundle.min.js"></script>
<!-- Select2 -->
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<!-- AdminLTE App -->
<script src="https://cdn.jsdelivr.net/npm/admin-lte@3.2.0/dist/js/adminlte.min.js"></script>
<!-- Alpine.js -->
<script src="https://cdn.jsdelivr.net/npm/alpinejs@2.8.2/dist/alpine.min.js"></script>
<!-- Custom JS -->
<script src="{{ asset('js/app.js') }}"></script>

@if(isset($pageScripts) && in_array('dashboard-charts', $pageScripts))
<!-- Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js@3.7.0/dist/chart.min.js"></script>
<!-- Dashboard Charts -->
<script src="{{ asset('js/dashboard-charts.js') }}"></script>
@endif

<!-- Scripts personnalisés par page -->

@stack('scripts')
</body>
</html>
