<?php

namespace Database\Seeders;

use App\Models\Campaign;
use App\Models\User;
use Illuminate\Database\Seeder;
use DateTime;

class CampaignSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Récupérer les IDs des administrateurs et superviseurs
        $adminIds = User::whereIn('role', ['super_admin', 'admin'])->pluck('id')->toArray();
        $supervisorIds = User::where('role', 'supervisor')->pluck('id')->toArray();
        
        // Statuts possibles pour les campagnes
        $statuses = ['draft', 'active', 'paused', 'completed'];
        
        // Plateformes possibles
        $platforms = ['instagram', 'facebook', 'twitter', 'tiktok', 'youtube', 'linkedin', 'snapchat'];
        
        // Noms de campagnes prédéfinis
        $campaignNames = [
            'Orange Connect', 'Digital Boost', 'Social Impact', 'Future Tech', 'Orange Innovation',
            'Smart Living', 'Green Initiative', 'Youth Connect', 'Business Solutions', 'Orange Family',
            'Tech Revolution', 'Orange Lifestyle', 'Community Power', 'Digital Freedom', 'Orange Horizon'
        ];
        
        // Périodes pour les noms de campagnes
        $periods = ['2024', '2025', 'Q1', 'Q2', 'Q3', 'Q4'];
        
        // Descriptions prédéfinies
        $descriptions = [
            'Campagne visant à promouvoir les nouveaux services connectés d\'Orange auprès d\'une audience jeune et dynamique.',
            'Stratégie de communication pour augmenter la visibilité de la marque sur les réseaux sociaux et toucher de nouveaux segments de marché.',
            'Campagne de sensibilisation aux initiatives environnementales et sociales d\'Orange.',
            'Promotion des dernières innovations technologiques et des services numériques avancés.',
            'Campagne ciblée pour le lancement de nouveaux forfaits et offres spéciales.'
        ];
        
        // Audiences cibles prédéfinies
        $audiences = ['13-17 ans', '18-24 ans', '25-34 ans', '35-44 ans', '45+ ans', 'Tous publics'];
        
        // Budgets prédéfinis
        $budgets = [50000, 75000, 100000, 150000, 200000, 250000];
        
        // Créer 15 campagnes avec différents statuts
        for ($i = 1; $i <= 15; $i++) {
            // Distribuer les statuts de manière équilibrée
            $statusIndex = ($i - 1) % count($statuses);
            $status = $statuses[$statusIndex];
            
            // Déterminer les dates en fonction du statut
            $now = new DateTime();
            $startDate = null;
            $endDate = null;
            
            switch ($status) {
                case 'draft':
                    $startDate = (new DateTime())->modify('+' . ($i % 14 + 7) . ' days'); // entre 7 et 21 jours dans le futur
                    $endDate = clone $startDate;
                    $endDate->modify('+' . ($i % 60 + 30) . ' days'); // entre 30 et 90 jours après le début
                    break;
                case 'active':
                    $startDate = (new DateTime())->modify('-' . ($i % 14) . ' days'); // entre 0 et 14 jours dans le passé
                    $endDate = (new DateTime())->modify('+' . ($i % 60 + 7) . ' days'); // entre 7 et 67 jours dans le futur
                    break;
                case 'paused':
                    $startDate = (new DateTime())->modify('-' . ($i % 21 + 7) . ' days'); // entre 7 et 28 jours dans le passé
                    $endDate = (new DateTime())->modify('+' . ($i % 21 + 7) . ' days'); // entre 7 et 28 jours dans le futur
                    break;
                case 'completed':
                    $startDate = (new DateTime())->modify('-' . ($i % 60 + 30) . ' days'); // entre 30 et 90 jours dans le passé
                    $endDate = (new DateTime())->modify('-' . ($i % 28 + 1) . ' days'); // entre 1 et 29 jours dans le passé
                    break;
            }
            
            // Sélectionner des plateformes de manière déterministe
            $numPlatforms = ($i % 4) + 1; // Entre 1 et 4 plateformes
            $selectedPlatforms = array_slice($platforms, 0, $numPlatforms);
            
            // Choisir un admin et un superviseur de manière déterministe
            $adminId = $adminIds[$i % count($adminIds)];
            $supervisorId = $supervisorIds[$i % count($supervisorIds)];
            
            // Créer la campagne
            Campaign::create([
                'name' => $campaignNames[$i-1] . ' ' . $periods[$i % count($periods)],
                'description' => $descriptions[$i % count($descriptions)],
                'status' => $status,
                'start_date' => $startDate,
                'end_date' => $endDate,
                'platforms' => json_encode($selectedPlatforms),
                'target_audience' => $audiences[$i % count($audiences)],
                'budget' => $budgets[$i % count($budgets)],
                'supervisor_id' => $supervisorId,
                'created_by' => $adminId,
                'updated_by' => $adminId,
                'objectives' => json_encode([
                    'reach' => 10000 * ($i + 1),
                    'engagement' => 1000 * ($i + 1),
                    'conversions' => 100 * ($i + 1)
                ]),
            ]);
        }
    }
}
