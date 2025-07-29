<?php

namespace Database\Seeders;

use App\Models\Assignment;
use App\Models\Campaign;
use App\Models\Content;
use App\Models\Publication;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class PublicationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Truncate la table des publications pour éviter les doublons lors des re-seeds
        DB::table('publications')->truncate();
        
        // Récupérer les IDs des assignations acceptées ou complétées
        $assignmentIds = Assignment::whereIn('status', ['accepted', 'completed'])->pluck('id')->toArray();
        
        // Récupérer les IDs des contenus
        $contentIds = Content::pluck('id')->toArray();
        
        // Si pas de contenus, en créer un fictif
        if (empty($contentIds)) {
            $contentIds = [1]; // On utilisera un ID fictif
        }
        
        // Plateformes disponibles
        $platforms = ['instagram', 'facebook', 'twitter', 'tiktok', 'youtube', 'linkedin'];
        
        // Types de publications
        $postTypes = ['post', 'story', 'reel', 'video', 'other'];
        
        // Statuts possibles pour les publications
        $statuses = ['pending_verification', 'verified', 'rejected'];
        
        // Contenu de posts prédéfinis
        $postContents = [
            'Découvrez la nouvelle offre Orange ! #Orange #Partenariat',
            'Je suis ravi de vous présenter ce nouveau produit Orange qui va changer votre quotidien. #Ad #Orange',
            'Orange innove encore avec cette solution connectée. Qu\'en pensez-vous ? #InfluBoss #Sponsored',
            'Test du dernier service Orange, mon avis dans cette vidéo ! #Orange #Review',
            'Merci à Orange pour cette collaboration ! Découvrez leur nouvelle campagne. #Partenariat',
            'La fibre optique Orange, une expérience incroyable à la maison ! #Orange #InfluBoss',
            'Orange Business Services propose des solutions adaptées aux professionnels. #B2B #Orange',
            'Mon expérience avec la Livebox 6 d\'Orange. #Tech #Orange #Ad',
            'La 5G Orange, c\'est une révolution pour le quotidien ! #5G #Orange #Sponsored',
            'Orange s\'engage pour l\'environnement avec cette initiative. #RSE #Orange'
        ];
        
        // Créer au moins 50 publications
        for ($i = 1; $i <= 55; $i++) {
            // S'assurer qu'il y a des assignations disponibles
            if (empty($assignmentIds)) {
                break;
            }
            
            // Choisir une assignation de manière déterministe
            $assignmentId = $assignmentIds[$i % count($assignmentIds)];
            
            // Récupérer l'assignation pour avoir l'ID de l'influenceur
            $assignment = Assignment::find($assignmentId);
            
            if (!$assignment) {
                continue;
            }
            
            $influencerId = $assignment->influencer_id;
            
            // Choisir une plateforme de manière déterministe
            $platform = $platforms[$i % count($platforms)];
            
            // Choisir un type de publication de manière déterministe
            $postType = $postTypes[$i % count($postTypes)];
            
            // Choisir un statut de manière déterministe
            $status = $statuses[$i % count($statuses)];
            
            // Récupérer la campagne associée à cette assignation via l'assignment
            $campaignId = $assignment->campaign_id;
            $campaign = Campaign::find($campaignId);
            
            // Générer une date de publication cohérente avec les dates de la campagne
            $now = Carbon::now();
            $minDate = $now->copy()->subDays(30); // Par défaut: 30 jours dans le passé
            $maxDate = $now->copy(); // Par défaut: aujourd'hui
            
            if ($campaign) {
                if ($campaign->start_date) {
                    $campaignStart = Carbon::parse($campaign->start_date);
                    $minDate = max($campaignStart, $now->copy()->subDays(60));
                }
                if ($campaign->end_date) {
                    $campaignEnd = Carbon::parse($campaign->end_date);
                    if ($campaignEnd->isFuture()) {
                        $maxDate = min($campaignEnd, $now->copy());
                    } else {
                        $maxDate = $campaignEnd;
                    }
                }
            }
            
            // Générer une date entre min et max
            $diffInDays = $minDate->diffInDays($maxDate);
            $randomDays = $diffInDays > 0 ? rand(0, $diffInDays) : 0;
            $publishedAt = $minDate->copy()->addDays($randomDays);
            
            // Générer un ID de post unique
            $postId = Str::random(10);
            
            // Créer l'URL du post en fonction de la plateforme
            $postUrl = '';
            switch ($platform) {
                case 'instagram':
                    $postUrl = 'https://instagram.com/p/' . $postId;
                    break;
                case 'facebook':
                    $postUrl = 'https://facebook.com/post/' . $postId;
                    break;
                case 'twitter':
                    $postUrl = 'https://twitter.com/status/' . $postId;
                    break;
                case 'tiktok':
                    $postUrl = 'https://tiktok.com/@user/video/' . $postId;
                    break;
                case 'youtube':
                    $postUrl = 'https://youtube.com/watch?v=' . $postId;
                    break;
                case 'linkedin':
                    $postUrl = 'https://linkedin.com/posts/' . $postId;
                    break;
            }
            
            // Générer des métriques de performance basées sur l'index de la publication
            $metrics = [
                'likes' => 10 + ($i * 900) % 50000, // Entre 10 et 50000 likes
                'comments' => ($i * 90) % 5000, // Entre 0 et 5000 commentaires
                'shares' => ($i * 18) % 1000, // Entre 0 et 1000 partages
                'views' => 100 + ($i * 9000) % 500000, // Entre 100 et 500000 vues
                'clicks' => ($i * 180) % 10000, // Entre 0 et 10000 clics
            ];
            
            // Choisir un contenu de manière déterministe
            $contentId = !empty($contentIds) ? $contentIds[$i % count($contentIds)] : 1;
            
            // Générer du contenu pour le post
            $hashtags = ['#Orange', '#InfluBoss', '#Partenariat', '#Ad', '#Sponsored'];
            // Sélectionner entre 1 et 5 hashtags de manière déterministe
            $numHashtags = ($i % 5) + 1;
            $selectedHashtags = array_slice($hashtags, 0, $numHashtags);
            $postContent = $postContents[$i % count($postContents)] . ' ' . implode(' ', $selectedHashtags);
            
            // Déterminer si la publication a été vérifiée ou rejetée
            $verifiedBy = null;
            $verifiedAt = null;
            $rejectionReason = null;
            
            // Si la publication est vérifiée ou rejetée, ajouter les informations correspondantes
            if ($status === 'verified' || $status === 'rejected') {
                // Utiliser le superviseur associé à l'assignation si possible
                $verifiedBy = $assignment->supervisor_id;
                
                // Si pas de superviseur, prendre un administrateur
                if (!$verifiedBy) {
                    $verifierIds = User::whereIn('role', ['supervisor', 'admin', 'super_admin'])
                        ->where('is_active', true)
                        ->pluck('id')
                        ->toArray();
                    $verifiedBy = !empty($verifierIds) ? $verifierIds[$i % count($verifierIds)] : 1;
                }
                
                // Date de vérification: 1-3 jours après publication
                $verifiedAt = $publishedAt->copy()->addDays(($i % 3) + 1);
                
                // Si rejetée, ajouter une raison de rejet
                if ($status === 'rejected') {
                    $rejectionReasons = [
                        'Le contenu ne respecte pas les directives de la marque.',
                        'La qualité de l\'image ou de la vidéo est insuffisante.',
                        'Le message ne met pas suffisamment en avant les valeurs de la marque.',
                        'La publication contient des éléments non conformes à notre charte.',
                        'Le contenu ne correspond pas à la stratégie de communication définie.',
                    ];
                    $rejectionReason = $rejectionReasons[$i % count($rejectionReasons)];
                }
            }
            
            // Créer la publication
            try {
                Publication::create([
                    'assignment_id' => $assignmentId,
                    'content_id' => $contentId,
                    'influencer_id' => $influencerId,
                    'platform' => $platform,
                    'post_url' => $postUrl,
                    'post_id' => $postId,
                    'post_content' => $postContent,
                    'post_type' => $postType,
                    'published_at' => $publishedAt,
                    'status' => $status,
                    'verified_by' => $verifiedBy,
                    'verified_at' => $verifiedAt,
                    'rejection_reason' => $rejectionReason,
                    'created_by' => $influencerId,
                    'updated_by' => $verifiedBy ?? $influencerId,
                    'created_at' => $publishedAt,
                    'updated_at' => $verifiedAt ?? $publishedAt,
                ]);
            } catch (\Exception $e) {
                error_log("Erreur lors de la création de la publication #$i: " . $e->getMessage());
            }
        }
        
        // Log le nombre de publications créées
        $count = Publication::count();
        error_log("Création de $count publications réussie.");
    }
}
