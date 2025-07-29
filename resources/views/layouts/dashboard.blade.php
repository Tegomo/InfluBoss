<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>InfluBoss - @yield('title')</title>
    
    <!-- Styles -->
    <link href="{{ asset('css/app.css') }}" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            background-color: #f3f6f9;
        }
        .sidebar {
            background-color: #0c3b5e;
            color: white;
            height: 100vh;
            width: 240px;
            position: fixed;
            top: 0;
            left: 0;
        }
        .sidebar a {
            color: #b3c1d1;
            transition: all 0.3s;
            padding: 12px 15px;
            display: flex;
            align-items: center;
            margin: 5px 0;
        }
        .sidebar a:hover, .sidebar a.active {
            color: white;
            background-color: rgba(255,255,255,0.1);
            border-left: 3px solid #ff9d00;
        }
        .sidebar a i {
            margin-right: 10px;
            width: 20px;
            text-align: center;
        }
        .user-profile {
            padding: 20px 15px;
            text-align: center;
            border-bottom: 1px solid rgba(255,255,255,0.1);
        }
        .user-avatar {
            height: 70px;
            width: 70px;
            border-radius: 50%;
            margin: 0 auto 10px;
            background-color: #ffffff;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 2rem;
            color: #0c3b5e;
        }
        .stat-card {
            background-color: white;
            border-radius: 10px;
            padding: 20px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            transition: all 0.3s;
        }
        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }
        .chart-container {
            background-color: white;
            border-radius: 10px;
            padding: 20px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            margin-top: 25px;
        }
        .circular-progress {
            width: 120px;
            height: 120px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            background: conic-gradient(#ff9d00 0%, #ff9d00 45%, #e6e6e6 45%, #e6e6e6 100%);
            position: relative;
        }
        .circular-progress::before {
            content: '';
            position: absolute;
            width: 90px;
            height: 90px;
            border-radius: 50%;
            background-color: white;
        }
        .progress-value {
            position: relative;
            font-size: 1.5rem;
            font-weight: bold;
            color: #0c3b5e;
        }
    </style>
</head>
<body>
    <!-- Sidebar -->
    <aside class="sidebar">
        <div class="user-profile">
            <div class="user-avatar">
                <i class="fas fa-user"></i>
            </div>
            <h3 class="text-lg font-bold mt-2">{{ Auth::user()->name }}</h3>
            <p class="text-sm opacity-75">{{ Auth::user()->email }}</p>
        </div>
        
        <nav class="mt-8">
            <a href="{{ route('dashboard') }}" class="{{ request()->routeIs('dashboard') ? 'active' : '' }}">
                <i class="fas fa-home"></i>
                <span>Accueil</span>
            </a>
            
            @can('viewAny', \App\Models\User::class)
            <a href="{{ route('users.index') }}" class="{{ request()->routeIs('users.*') ? 'active' : '' }}">
                <i class="fas fa-users"></i>
                <span>Utilisateurs</span>
            </a>
            @endcan
            
            @can('viewAny', \App\Models\Campaign::class)
            <a href="{{ route('campaigns.index') }}" class="{{ request()->routeIs('campaigns.*') ? 'active' : '' }}">
                <i class="fas fa-bullhorn"></i>
                <span>Campagnes</span>
            </a>
            @endcan
            
            @can('viewAny', \App\Models\Content::class)
            <a href="{{ route('contents.index') }}" class="{{ request()->routeIs('contents.*') ? 'active' : '' }}">
                <i class="fas fa-images"></i>
                <span>Contenus</span>
            </a>
            @endcan
            
            @can('viewAny', \App\Models\Assignment::class)
            <a href="{{ route('assignments.index') }}" class="{{ request()->routeIs('assignments.*') ? 'active' : '' }}">
                <i class="fas fa-tasks"></i>
                <span>Missions</span>
            </a>
            @endcan
            
            @can('viewAny', \App\Models\Publication::class)
            <a href="{{ route('publications.index') }}" class="{{ request()->routeIs('publications.*') ? 'active' : '' }}">
                <i class="fas fa-newspaper"></i>
                <span>Publications</span>
            </a>
            @endcan
            
            <a href="{{ route('logout') }}" onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                <i class="fas fa-sign-out-alt"></i>
                <span>Déconnexion</span>
            </a>
            <form id="logout-form" action="{{ route('logout') }}" method="POST" class="hidden">
                @csrf
            </form>
        </nav>
    </aside>

    <!-- Main Content -->
    <main style="margin-left: 240px; padding: 20px 30px;">
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-2xl font-bold text-gray-800">@yield('title')</h1>
            <div class="flex items-center space-x-4">
                <div class="relative">
                    <i class="fas fa-bell text-gray-500"></i>
                    <span class="absolute -top-1 -right-1 bg-red-500 text-white rounded-full h-4 w-4 flex items-center justify-center text-xs">3</span>
                </div>
                <div class="bg-white rounded-full p-2 shadow-sm">
                    <i class="fas fa-cog text-gray-500"></i>
                </div>
            </div>
        </div>
        
        @yield('content')
    </main>

    <!-- Scripts -->
    <script src="{{ asset('js/app.js') }}"></script>
    <script src="https://cdn.jsdelivr.net/npm/alpinejs@2.8.2/dist/alpine.min.js"></script>
</body>
</html>
