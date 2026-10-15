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

    // Catégories d'ambassadeurs définies par Orange (clé stockée dans users.categories => libellé affiché).
    'categories' => [
        'high_level' => 'High Level',
        'business' => 'Business',
        'story_tellers' => 'Story Tellers',
        'noso' => 'NOSO',
        'anen' => 'ANEN',
        'orange_money' => 'Orange Money',
        'pages_populaires' => 'Pages populaires',
        'lifestyle_mass_market' => 'Lifestyle Mass Market',
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

    // Paiements. « En retard » n'est pas stocké : en attente avec échéance dépassée.
    'payment_statuses' => [
        'pending' => ['label' => 'En attente', 'badge' => 'warning'],
        'paid' => ['label' => 'Payé', 'badge' => 'success'],
        'cancelled' => ['label' => 'Annulé', 'badge' => 'secondary'],
    ],

    'payment_methods' => [
        'bank_transfer' => 'Virement bancaire',
        'mobile_money' => 'Mobile Money',
        'cash' => 'Espèces',
        'cheque' => 'Chèque',
        'other' => 'Autre',
    ],

    // Fichiers joints (contrats, documents légaux) : extensions et taille maximale en Ko.
    'upload_mimes' => 'pdf,jpg,jpeg,png',
    'upload_max_kb' => 10240,

    // Mot de passe attribué à un ambassadeur créé depuis le formulaire ou importé en CSV (pas de champ mot de passe).
    'default_influencer_password' => env('INFLUBOSS_DEFAULT_INFLUENCER_PASSWORD', 'Pass@2026'),

    // Comptes créés à l'initialisation de la base (InitialAccountsSeeder). Adresses modifiables par variables d'environnement.
    'initial_accounts' => [
        ['role' => 'super_admin', 'name' => 'Administrateur McCANN', 'email' => env('INFLUBOSS_ADMIN_EMAIL', 'admin@influboss.cm')],
        ['role' => 'client', 'name' => 'Orange Cameroun', 'email' => env('INFLUBOSS_CLIENT_EMAIL', 'orange@influboss.cm')],
        ['role' => 'supervisor', 'name' => 'Superviseur 1', 'email' => env('INFLUBOSS_SUPERVISOR1_EMAIL', 'superviseur1@influboss.cm')],
        ['role' => 'supervisor', 'name' => 'Superviseur 2', 'email' => env('INFLUBOSS_SUPERVISOR2_EMAIL', 'superviseur2@influboss.cm')],
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
