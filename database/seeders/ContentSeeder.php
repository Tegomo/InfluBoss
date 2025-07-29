<?php

namespace Database\Seeders;

use App\Models\Campaign;
use App\Models\Content;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use DateTime;

class ContentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Récupérer les IDs des campagnes
        $campaignIds = Campaign::pluck('id')->toArray();
        
        // Types de contenu possibles
        $contentTypes = ['image', 'video', 'text', 'link', 'other'];
        
        // Fonction pour mapper les anciens types vers les types autorisés
        function mapContentType($type) {
            $mapping = [
                'story' => 'image', // ou 'video'
                'reel' => 'video',
                'carousel' => 'image',
            ];
            return $mapping[$type] ?? $type;
        }
        
        // Statuts possibles
        $statuses = [Content::STATUS_DRAFT, Content::STATUS_APPROVED, Content::STATUS_ARCHIVED];
        
        // Titres prédéfinis pour les contenus
        $titles = [
            'Découvrez notre nouvelle offre', 'Orange Innovation 2025', 'Connectez-vous au futur',
            'Votre expérience numérique', 'Orange pour les professionnels', 'Simplifiez votre quotidien',
            'La fibre optique pour tous', 'Solutions entreprises', 'Orange Family', 'Offres spéciales été',
            'Forfaits mobiles illimités', 'Orange TV', 'Services connectés', 'Orange Money',
            'Nos engagements RSE', 'Orange Business Services', 'Livebox 6', 'Orange 5G',
            'Accessoires connectés', 'Orange Cloud'
        ];
        
        // Descriptions prédéfinies
        $descriptions = [
            'Découvrez notre nouvelle gamme de services connectés pour toute la famille.',
            'Orange vous propose des solutions innovantes pour améliorer votre quotidien numérique.',
            'Profitez de la meilleure expérience internet avec nos offres fibre optique.',
            'Nos forfaits mobiles s\'adaptent à tous vos besoins, en France comme à l\'international.',
            'Orange Business Services accompagne les entreprises dans leur transformation numérique.'
        ];
        
        // Options de directives prédéfinies
        $allGuidelines = [
            'utiliser le filtre Instagram "Warm"',
            'utiliser le filtre Instagram "Cold"',
            'utiliser le filtre Instagram "Vintage"',
            'utiliser le filtre Instagram "Modern"',
            'utiliser le filtre Instagram "Natural"',
            'mentionner #OrangeConnect',
            'mentionner #InnovationOrange',
            'mentionner #OrangeFibre',
            'taguer @orange_officiel',
            'publier entre 10h et 20h',
            'publier entre 8h et 18h',
            'inclure un appel à l\'action',
            'montrer le produit en situation d\'utilisation',
            'mettre en avant le logo Orange',
            'utiliser la palette de couleurs de la marque',
            'filmer en format portrait',
            'filmer en format paysage',
            'filmer en format carré',
            'durée recommandée: 30 secondes',
            'durée recommandée: 60 secondes',
            'durée recommandée: 90 secondes'
        ];
        
        // Créer 20 contenus variés
        for ($i = 1; $i <= 20; $i++) {
            // Choisir une campagne de manière déterministe
            $campaignId = $campaignIds[$i % count($campaignIds)];
            
            // Choisir un type de contenu de manière déterministe
            $originalTypes = ['image', 'video', 'carousel', 'text', 'story', 'reel'];
            $originalType = $originalTypes[$i % count($originalTypes)];
            $contentType = mapContentType($originalType);
            
            // Choisir un statut de manière déterministe
            $status = $statuses[$i % count($statuses)];
            
            // Utiliser un titre et une description prédéfinis
            $title = $titles[$i-1];
            $description = $descriptions[$i % count($descriptions)];
            
            // Générer un chemin de fichier en fonction du type
            $filePath = null;
            $randomNumber = 10000 + $i;
            switch ($contentType) {
                case 'video':
                    $filePath = 'content-files/' . Str::slug($title) . '-' . $randomNumber . '.mp4';
                    break;
                case 'image':
                    $filePath = 'content-files/' . Str::slug($title) . '-' . $randomNumber . '.jpg';
                    break;
                case 'carousel':
                    $filePath = 'content-files/' . Str::slug($title) . '-' . $randomNumber . '/'; // dossier pour les images du carousel
                    break;
            }
            
            // Sélectionner des directives de manière déterministe
            $numGuidelines = 3 + ($i % 4); // Entre 3 et 6 directives
            $guidelines = [];
            for ($j = 0; $j < $numGuidelines; $j++) {
                $index = ($i + $j) % count($allGuidelines);
                $guidelines[] = $allGuidelines[$index];
            }
            
            // Date de publication
            $now = new DateTime();
            $daysOffset = -30 + ($i * 3); // Entre -30 et +30 jours par rapport à aujourd'hui
            $publicationDate = (clone $now)->modify($daysOffset . ' days');
            
            // Créer le contenu
            Content::create([
                'title' => $title,
                'description' => $description,
                'campaign_id' => $campaignId,
                'type' => $contentType,
                'content_text' => $contentType === 'text' ? 'Contenu texte pour ' . $title . '. Ce contenu est destiné à être utilisé dans le cadre de la campagne marketing. Il met en avant les valeurs de la marque Orange et ses engagements envers ses clients.' : null,
                'file_path' => $filePath,
                'guidelines' => json_encode($guidelines),
                'status' => $status,
                'publication_date' => $publicationDate,
                'created_by' => 1,
                'updated_by' => 1,
            ]);
        }
        
        // Ajouter quelques contenus spécifiques pour les campagnes importantes
        Content::create([
            'title' => 'Message de Noël Orange',
            'description' => 'Message de Noël pour les réseaux sociaux',
            'campaign_id' => $campaignIds[0],
            'type' => 'video',
            'content_text' => null,
            'file_path' => 'content-files/noel-2024.mp4',
            'guidelines' => json_encode([
                'utiliser le filtre Instagram "Warm"',
                'mentionner #OrangeNoel2024',
                'taguer @orange_officiel',
                'publier entre 18h et 20h'
            ]),
            'status' => Content::STATUS_APPROVED,
            'publication_date' => now()->addDays(1),
            'created_by' => 1,
            'updated_by' => 1,
        ]);

        Content::create([
            'title' => 'Story Noël Orange',
            'description' => 'Story Instagram pour Noël',
            'campaign_id' => $campaignIds[0],
            'type' => 'video',  // Changé 'story' en 'video' car c'est un format vidéo pour stories Instagram
            'content_text' => null,
            'file_path' => 'content-files/noel-story-2024.mp4',
            'guidelines' => json_encode([
                'utiliser le filtre Instagram "Warm"',
                'mentionner #OrangeNoel2024',
                'taguer @orange_officiel',
                'publier entre 18h et 20h'
            ]),
            'status' => Content::STATUS_APPROVED,
            'publication_date' => now()->addDays(1),
            'created_by' => 1,
            'updated_by' => 1,
        ]);

        // Contenu pour la Campagne 2
        Content::create([
            'title' => 'Message Printemps Orange',
            'description' => 'Message de printemps pour les réseaux sociaux',
            'campaign_id' => isset($campaignIds[1]) ? $campaignIds[1] : 1,
            'type' => 'image',
            'content_text' => null,
            'file_path' => 'content-files/printemps-2025.jpg',
            'guidelines' => json_encode([
                'utiliser le filtre Instagram "Spring"',
                'mentionner #OrangePrintemps2025',
                'taguer @orange_officiel',
                'publier entre 12h et 14h'
            ]),
            'status' => Content::STATUS_DRAFT,
            'publication_date' => now()->addMonths(4),
            'created_by' => 1,
            'updated_by' => 1,
        ]);
    }
}
