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
