<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Statistiques InfluBoss - {{ date('d/m/Y') }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            margin: 0;
            padding: 20px;
            color: #333;
        }
        
        .header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 2px solid #007bff;
            padding-bottom: 20px;
        }
        
        .header h1 {
            color: #007bff;
            margin: 0;
            font-size: 24px;
        }
        
        .header p {
            margin: 5px 0;
            color: #666;
        }
        
        .section {
            margin-bottom: 30px;
            page-break-inside: avoid;
        }
        
        .section-title {
            background-color: #007bff;
            color: white;
            padding: 10px;
            margin-bottom: 15px;
            font-size: 16px;
            font-weight: bold;
        }
        
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
            margin-bottom: 20px;
        }
        
        .stat-card {
            border: 1px solid #ddd;
            padding: 15px;
            border-radius: 5px;
            background-color: #f8f9fa;
        }
        
        .stat-card h4 {
            margin: 0 0 10px 0;
            color: #007bff;
            font-size: 14px;
        }
        
        .stat-value {
            font-size: 24px;
            font-weight: bold;
            color: #333;
        }
        
        .table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        
        .table th,
        .table td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }
        
        .table th {
            background-color: #007bff;
            color: white;
            font-weight: bold;
        }
        
        .table tr:nth-child(even) {
            background-color: #f8f9fa;
        }
        
        .monthly-data {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 10px;
            margin-top: 15px;
        }
        
        .month-item {
            text-align: center;
            padding: 8px;
            border: 1px solid #ddd;
            border-radius: 3px;
        }
        
        .month-name {
            font-weight: bold;
            color: #007bff;
        }
        
        .month-value {
            font-size: 18px;
            color: #333;
        }
        
        .footer {
            position: fixed;
            bottom: 20px;
            left: 20px;
            right: 20px;
            text-align: center;
            font-size: 10px;
            color: #666;
            border-top: 1px solid #ddd;
            padding-top: 10px;
        }
        
        .page-break {
            page-break-before: always;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Rapport de Statistiques InfluBoss</h1>
        <p>Généré le {{ date('d/m/Y à H:i') }}</p>
        <p>Période : Année {{ date('Y') }}</p>
    </div>

    <!-- Statistiques des utilisateurs -->
    <div class="section">
        <div class="section-title">📊 Statistiques des Utilisateurs</div>
        
        <div class="stats-grid">
            <div class="stat-card">
                <h4>Total Utilisateurs</h4>
                <div class="stat-value">{{ $userStats['total'] }}</div>
            </div>
            <div class="stat-card">
                <h4>Ambassadeurs</h4>
                <div class="stat-value">{{ $userStats['influencers'] }}</div>
            </div>
            <div class="stat-card">
                <h4>Superviseurs</h4>
                <div class="stat-value">{{ $userStats['supervisors'] }}</div>
            </div>
            <div class="stat-card">
                <h4>Administrateurs</h4>
                <div class="stat-value">{{ $userStats['admins'] }}</div>
            </div>
            <div class="stat-card">
                <h4>Utilisateurs Actifs</h4>
                <div class="stat-value">{{ $userStats['active'] }}</div>
            </div>
            <div class="stat-card">
                <h4>Utilisateurs Inactifs</h4>
                <div class="stat-value">{{ $userStats['inactive'] }}</div>
            </div>
        </div>
        
        <p><strong>Activité récente :</strong> {{ $userStats['recentlyActive'] }} utilisateurs créés dans les 30 derniers jours</p>
    </div>

    <!-- Statistiques des campagnes -->
    <div class="section">
        <div class="section-title">🎯 Statistiques des Campagnes</div>
        
        <div class="stats-grid">
            <div class="stat-card">
                <h4>Total Campagnes</h4>
                <div class="stat-value">{{ $campaignStats['total'] }}</div>
            </div>
            <div class="stat-card">
                <h4>Campagnes Actives</h4>
                <div class="stat-value">{{ $campaignStats['active'] }}</div>
            </div>
            <div class="stat-card">
                <h4>Brouillons</h4>
                <div class="stat-value">{{ $campaignStats['draft'] }}</div>
            </div>
            <div class="stat-card">
                <h4>Terminées</h4>
                <div class="stat-value">{{ $campaignStats['completed'] }}</div>
            </div>
            <div class="stat-card">
                <h4>Archivées</h4>
                <div class="stat-value">{{ $campaignStats['archived'] }}</div>
            </div>
        </div>
        
        <h4>Évolution mensuelle des campagnes :</h4>
        <div class="monthly-data">
            @php
                $months = [
                    1 => 'Jan', 2 => 'Fév', 3 => 'Mar', 4 => 'Avr',
                    5 => 'Mai', 6 => 'Jun', 7 => 'Jul', 8 => 'Aoû',
                    9 => 'Sep', 10 => 'Oct', 11 => 'Nov', 12 => 'Déc'
                ];
            @endphp
            @foreach($months as $num => $name)
                <div class="month-item">
                    <div class="month-name">{{ $name }}</div>
                    <div class="month-value">{{ $campaignStats['byMonth'][$num] ?? 0 }}</div>
                </div>
            @endforeach
        </div>
    </div>

    <!-- Statistiques des contenus -->
    <div class="section">
        <div class="section-title">📝 Statistiques des Contenus</div>
        
        <div class="stats-grid">
            <div class="stat-card">
                <h4>Total Contenus</h4>
                <div class="stat-value">{{ $contentStats['total'] }}</div>
            </div>
            <div class="stat-card">
                <h4>Contenus Approuvés</h4>
                <div class="stat-value">{{ $contentStats['approved'] }}</div>
            </div>
            <div class="stat-card">
                <h4>Brouillons</h4>
                <div class="stat-value">{{ $contentStats['draft'] }}</div>
            </div>
        </div>
        
        @if(!empty($contentStats['byType']))
            <h4>Répartition par type :</h4>
            <table class="table">
                <thead>
                    <tr>
                        <th>Type de contenu</th>
                        <th>Nombre</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($contentStats['byType'] as $type => $count)
                        <tr>
                            <td>{{ ucfirst($type) }}</td>
                            <td>{{ $count }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif
    </div>

    <div class="page-break"></div>

    <!-- Statistiques des assignations -->
    <div class="section">
        <div class="section-title">📋 Statistiques des Assignations</div>
        
        <div class="stats-grid">
            <div class="stat-card">
                <h4>Total Assignations</h4>
                <div class="stat-value">{{ $assignmentStats['total'] }}</div>
            </div>
            <div class="stat-card">
                <h4>En Attente</h4>
                <div class="stat-value">{{ $assignmentStats['pending'] }}</div>
            </div>
            <div class="stat-card">
                <h4>Acceptées</h4>
                <div class="stat-value">{{ $assignmentStats['accepted'] }}</div>
            </div>
            <div class="stat-card">
                <h4>Rejetées</h4>
                <div class="stat-value">{{ $assignmentStats['rejected'] }}</div>
            </div>
            <div class="stat-card">
                <h4>Terminées</h4>
                <div class="stat-value">{{ $assignmentStats['completed'] }}</div>
            </div>
        </div>
    </div>

    <!-- Statistiques des publications -->
    <div class="section">
        <div class="section-title">📱 Statistiques des Publications</div>
        
        <div class="stats-grid">
            <div class="stat-card">
                <h4>Total Publications</h4>
                <div class="stat-value">{{ $publicationStats['total'] }}</div>
            </div>
            <div class="stat-card">
                <h4>En Attente</h4>
                <div class="stat-value">{{ $publicationStats['pending'] }}</div>
            </div>
            <div class="stat-card">
                <h4>Vérifiées</h4>
                <div class="stat-value">{{ $publicationStats['verified'] }}</div>
            </div>
            <div class="stat-card">
                <h4>Rejetées</h4>
                <div class="stat-value">{{ $publicationStats['rejected'] }}</div>
            </div>
        </div>
        
        @if(!empty($publicationStats['byPlatform']))
            <h4>Publications par plateforme :</h4>
            <table class="table">
                <thead>
                    <tr>
                        <th>Plateforme</th>
                        <th>Nombre de publications</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($publicationStats['byPlatform'] as $platform => $count)
                        <tr>
                            <td>{{ ucfirst($platform) }}</td>
                            <td>{{ $count }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif
    </div>

    <!-- Top Ambassadeurs -->
    <div class="section">
        <div class="section-title">🏆 Top 10 Ambassadeurs</div>
        
        @if($topInfluencers->count() > 0)
            <table class="table">
                <thead>
                    <tr>
                        <th>Rang</th>
                        <th>Nom</th>
                        <th>Email</th>
                        <th>Publications</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($topInfluencers as $index => $influencer)
                        <tr>
                            <td>{{ $index + 1 }}</td>
                            <td>{{ $influencer->name }}</td>
                            <td>{{ $influencer->email }}</td>
                            <td>{{ $influencer->publications_count }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @else
            <p>Aucun ambassadeur trouvé.</p>
        @endif
    </div>

    <!-- Top Superviseurs -->
    <div class="section">
        <div class="section-title">👥 Top 10 Superviseurs</div>
        
        @if($topSupervisors->count() > 0)
            <table class="table">
                <thead>
                    <tr>
                        <th>Rang</th>
                        <th>Nom</th>
                        <th>Email</th>
                        <th>Assignations</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($topSupervisors as $index => $supervisor)
                        <tr>
                            <td>{{ $index + 1 }}</td>
                            <td>{{ $supervisor->name }}</td>
                            <td>{{ $supervisor->email }}</td>
                            <td>{{ $supervisor->supervisor_assignments_count }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @else
            <p>Aucun superviseur trouvé.</p>
        @endif
    </div>

    <!-- Top Campagnes -->
    <div class="section">
        <div class="section-title">🎯 Top 10 Campagnes</div>
        
        @if($topCampaigns->count() > 0)
            <table class="table">
                <thead>
                    <tr>
                        <th>Rang</th>
                        <th>Nom de la campagne</th>
                        <th>Statut</th>
                        <th>Publications</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($topCampaigns as $index => $campaign)
                        <tr>
                            <td>{{ $index + 1 }}</td>
                            <td>{{ $campaign->name }}</td>
                            <td>{{ ucfirst($campaign->status) }}</td>
                            <td>{{ $campaign->publications_count }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @else
            <p>Aucune campagne trouvée.</p>
        @endif
    </div>

    <div class="footer">
        <p>Rapport généré par InfluBoss - {{ date('d/m/Y à H:i') }} - Page <span class="pagenum"></span></p>
    </div>
</body>
</html>
