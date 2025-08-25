<?php

namespace Database\Seeders;

use App\Models\Assignment;
use App\Models\Campaign;
use App\Models\Content;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class AssignmentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Truncate la table assignments pour éviter les doublons lors des re-seeds
        DB::table('assignments')->truncate();
        
        // Récupérer les IDs des campagnes actives ou en attente
        $campaignIds = Campaign::whereIn('status', ['active', 'draft'])->pluck('id')->toArray();
        if (empty($campaignIds)) {
            // Fallback sur toutes les campagnes si nécessaire
            $campaignIds = Campaign::pluck('id')->toArray();
        }
        
        // Récupérer les IDs des influenceurs actifs
        $influencerIds = User::where('role', 'influencer')->where('is_active', true)->pluck('id')->toArray();
        
        // Récupérer les IDs des superviseurs actifs
        $supervisorIds = User::where('role', 'supervisor')->where('is_active', true)->pluck('id')->toArray();
        
        // Récupérer les IDs des administrateurs
        $adminIds = User::whereIn('role', ['super_admin', 'admin'])->pluck('id')->toArray();
        
        // Récupérer les contenus approuvés par campagne
        $contentsByCampaign = [];
        foreach ($campaignIds as $campaignId) {
            $contentsByCampaign[$campaignId] = Content::where('campaign_id', $campaignId)
                ->where('status', Content::STATUS_APPROVED)
                ->pluck('id')
                ->toArray();
        }
        
        // Statuts possibles pour les assignations
        $statuses = ['pending', 'accepted', 'declined', 'completed'];
        
        // Notes prédéfinies pour les assignations
        $assignmentNotes = [
            'Influenceur recommandé par l\'agence partenaire.',
            'Collaboration précédente réussie avec cet influenceur.',
            'Attention particulière à porter sur le respect des directives de la marque.',
            'Influenceur à fort potentiel pour les campagnes futures.',
            null, // Pas de note pour certaines assignations
            'Première collaboration avec cet influenceur.',
            'Influenceur spécialisé dans le domaine technologique.',
            null,
            'Excellente audience dans la cible démographique 18-24 ans.',
            'Contenu à valider avant publication.'
        ];
        
        // Plateformes disponibles
        $availablePlatforms = ['instagram', 'tiktok', 'facebook', 'youtube', 'twitter', 'blog', 'other'];
        
        // Lier directement les influenceurs aux superviseurs sans passer par la table pivot
        // car la relation a changé
        $assignmentCount = 0;
        $maxAssignments = 40; // Limite pour éviter de créer trop d'entrées
        
        foreach ($influencerIds as $influencerIndex => $influencerId) {
            // Déterminer le superviseur pour cet influenceur
            $supervisorId = $supervisorIds[$influencerIndex % count($supervisorIds)];
            
            // Créer 1 à 3 assignations pour chaque influenceur
            $numAssignments = min(($influencerIndex % 3) + 1, count($campaignIds));
            
            for ($j = 0; $j < $numAssignments; $j++) {
                // Vérifier la limite
                if ($assignmentCount >= $maxAssignments) break;
                
                // Choisir une campagne de manière déterministe
                $campaignIndex = ($influencerIndex + $j) % count($campaignIds);
                $campaignId = $campaignIds[$campaignIndex];
                
                // Choisir un contenu si disponible pour cette campagne
                $contentId = null;
                if (!empty($contentsByCampaign[$campaignId])) {
                    $contentIndex = ($influencerIndex + $j) % count($contentsByCampaign[$campaignId]);
                    $contentId = $contentsByCampaign[$campaignId][$contentIndex];
                }
                
                // Choisir un statut de manière déterministe
                $statusIndex = ($influencerIndex + $j) % count($statuses);
                $status = $statuses[$statusIndex];
                
                // Déterminer les dates en fonction du statut
                $now = Carbon::now();
                $assignedAt = Carbon::now()->subDays(($influencerIndex + $j) % 60); // Entre 0 et 60 jours dans le passé
                $acceptedAt = null;
                
                if ($status !== 'pending' && $status !== 'declined') {
                    $acceptedAt = (clone $assignedAt)->addDays((($influencerIndex + $j) % 5) + 1); // 1 à 5 jours après l'assignation
                }
                
                // Choisir un admin de manière déterministe
                $adminId = $adminIds[($influencerIndex + $j) % count($adminIds)];
                
                // Compensation entre 500 et 5000 euros
                $compensation = 500 + (($influencerIndex + $j) * 75) % 4500;
                
                // Générer des instructions et descriptions réalistes
                $campaign = Campaign::find($campaignId);
                $campaignName = $campaign ? $campaign->name : 'Campagne';
                
                $instructions = "Publiez " . rand(1, 3) . " posts mettant en avant nos produits/services. \n\n"
                    . "Utilisez les hashtags #" . str_replace([' ', '-'], '', $campaignName) . " et #ad. \n\n"
                    . "Assurez-vous que le branding Orange est clairement visible. \n\n"
                    . "Soumettez vos publications pour validation avant publication finale.";
                
                $description = "Promotion de la campagne '$campaignName' auprès de votre audience. "
                    . "Mettez en valeur notre image de marque et nos produits de manière authentique.";
                
                // Date limite : entre 7 et 21 jours après l'assignation
                $deadline = (clone $assignedAt)->addDays(rand(7, 21));
                
                // Sélectionner 2 à 4 plateformes aléatoirement
                $numPlatforms = rand(2, 4);
                $platformsShuffled = $availablePlatforms;
                shuffle($platformsShuffled);
                $platforms = array_slice($platformsShuffled, 0, $numPlatforms);
                
                // Nombre de publications requises
                $requiredPosts = rand(1, 4);
                
                // Créer l'assignation
                // Créer un tableau avec les données d'assignation sans le champ problématique accepted_at
                $assignmentData = [
                    'campaign_id' => $campaignId,
                    'influencer_id' => $influencerId,
                    'supervisor_id' => $supervisorId,
                    'content_id' => $contentId,
                    'status' => $status,
                    'assigned_at' => $assignedAt,
                    'created_by' => $adminId,
                    'compensation' => $compensation,
                    'notes' => $assignmentNotes[$influencerIndex % count($assignmentNotes)],
                    'created_at' => $assignedAt,
                    'updated_at' => $assignedAt,
                    'deadline' => $deadline,
                    'description' => $description,
                    'instructions' => $instructions,
                    'required_posts' => $requiredPosts,
                    'platforms' => json_encode($platforms),
                ];
                
                // Ajouter directement via DB pour éviter les problèmes avec les colonnes manquantes
                DB::table('assignments')->insert($assignmentData);
                
                $assignmentCount++;
            }
        }
        
        // Log le nombre d'assignments créés
        error_log("Création de $assignmentCount assignments réussie.");
    }
}
