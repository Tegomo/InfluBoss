<?php

/*
|--------------------------------------------------------------------------
| Référentiels métier InfluBoss
|--------------------------------------------------------------------------
|
| Catégories (niches) des influenceurs et plateformes sociales gérées.
| Les clés sont techniques (stockées en base, exportées en CSV) ; les
| libellés sont affichés dans l'interface.
|
*/

return [

    'categories' => [
        'lifestyle' => 'Lifestyle',
        'humour' => 'Humour',
        'tech' => 'Tech',
        'musique' => 'Musique',
        'mode' => 'Mode',
        'beaute' => 'Beauté',
        'sport' => 'Sport',
        'cuisine' => 'Cuisine',
        'voyage' => 'Voyage',
        'gaming' => 'Gaming',
        'education' => 'Éducation',
        'business' => 'Business / Entrepreneuriat',
        'actualite' => 'Actualité / Médias',
        'divertissement' => 'Divertissement',
        'famille' => 'Famille / Parentalité',
        'sante' => 'Santé / Bien-être',
        'art' => 'Art / Culture',
        'autre' => 'Autre',
    ],

    'platforms' => [
        'instagram' => ['label' => 'Instagram', 'icon' => 'fab fa-instagram', 'color' => '#E1306C', 'url_prefix' => 'https://www.instagram.com/'],
        'tiktok' => ['label' => 'TikTok', 'icon' => 'fab fa-tiktok', 'color' => '#010101', 'url_prefix' => 'https://www.tiktok.com/@'],
        'youtube' => ['label' => 'YouTube', 'icon' => 'fab fa-youtube', 'color' => '#FF0000', 'url_prefix' => 'https://www.youtube.com/@'],
        'facebook' => ['label' => 'Facebook', 'icon' => 'fab fa-facebook', 'color' => '#1877F2', 'url_prefix' => 'https://www.facebook.com/'],
        'x' => ['label' => 'X (Twitter)', 'icon' => 'fab fa-twitter', 'color' => '#14171A', 'url_prefix' => 'https://x.com/'],
        'linkedin' => ['label' => 'LinkedIn', 'icon' => 'fab fa-linkedin', 'color' => '#0A66C2', 'url_prefix' => 'https://www.linkedin.com/in/'],
    ],

    'contract_types' => [
        'exclusive' => 'Exclusif',
        'one_off' => 'Ponctuel',
        'ambassador' => 'Ambassadeur',
        'other' => 'Autre',
    ],

    // Statut => [libellé, couleur de badge Bootstrap]
    'contract_statuses' => [
        'negotiation' => ['label' => 'En négociation', 'badge' => 'warning'],
        'active' => ['label' => 'Actif', 'badge' => 'success'],
        'expired' => ['label' => 'Expiré', 'badge' => 'secondary'],
        'terminated' => ['label' => 'Résilié', 'badge' => 'danger'],
    ],

    // Nombre de jours avant la fin d'un contrat actif auxquels une alerte est envoyée.
    'contract_alert_days' => [30, 15, 7],

    'legal_document_types' => [
        'identity' => "Pièce d'identité",
        'rib' => 'RIB',
        'tax' => 'Attestation fiscale / NIU',
        'other' => 'Autre',
    ],

    // Livrables d'une mission (cahier des charges)
    'deliverable_types' => [
        'post' => 'Publication',
        'story' => 'Story',
        'reel' => 'Reel',
        'video' => 'Vidéo',
        'live' => 'Live',
        'other' => 'Autre',
    ],

    // Statut => [libellé, couleur de badge Bootstrap]
    'deliverable_statuses' => [
        'todo' => ['label' => 'À faire', 'badge' => 'secondary'],
        'in_progress' => ['label' => 'En cours', 'badge' => 'info'],
        'delivered' => ['label' => 'Livré', 'badge' => 'primary'],
        'validated' => ['label' => 'Validé', 'badge' => 'success'],
    ],

    // Fichiers joints (contrats, documents légaux) : extensions et taille maximale en Ko.
    'upload_mimes' => 'pdf,jpg,jpeg,png',
    'upload_max_kb' => 10240,

    // Clés historiques (users.social_links, import CSV) vers les clés actuelles.
    'legacy_platform_keys' => [
        'twitter' => 'x',
    ],

    // Anciennes colonnes de la table users vers la plateforme correspondante.
    'legacy_handle_columns' => [
        'instagram_handle' => 'instagram',
        'twitter_handle' => 'x',
        'facebook_handle' => 'facebook',
        'youtube_channel' => 'youtube',
        'tiktok_handle' => 'tiktok',
        'linkedin_profile' => 'linkedin',
    ],

];
