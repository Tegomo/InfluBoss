<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Carbon\Carbon;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Création des utilisateurs administratifs
        
        // Super Admin - Utilisateur avec tous les droits
        User::create([
            'name' => 'Super Admin',
            'pseudo' => 'superadmin',
            'email' => 'superadmin@influboss.com',
            'password' => Hash::make('password'),
            'phone' => '+33601020304',
            'birthdate' => Carbon::parse('1985-01-15'),
            'city' => 'Paris',
            'role' => 'super_admin',
            'is_active' => true,
        ]);

        // Administrateur - Gestion quotidienne de la plateforme
        User::create([
            'name' => 'Administrateur',
            'pseudo' => 'admin',
            'email' => 'admin@influboss.com',
            'password' => Hash::make('password'),
            'phone' => '+33601020305',
            'birthdate' => Carbon::parse('1988-05-20'),
            'city' => 'Lyon',
            'role' => 'admin',
            'is_active' => true,
        ]);

        // Création de 6 superviseurs (5 actifs, 1 inactif)
        $supervisorPseudos = ['sup_boss', 'sup_manager', 'sup_coach', 'sup_chief', 'sup_lead', 'sup_inactive'];
        $cities = ['Paris', 'Lyon', 'Marseille', 'Bordeaux', 'Lille', 'Nantes', 'Strasbourg', 'Toulouse', 'Nice', 'Rennes'];
        
        for ($i = 1; $i <= 6; $i++) {
            $birthYear = rand(1975, 1995);
            $birthMonth = rand(1, 12);
            $birthDay = rand(1, 28);
            
            User::create([
                'name' => 'Superviseur ' . $i,
                'pseudo' => $supervisorPseudos[$i-1],
                'email' => 'supervisor' . $i . '@influboss.com',
                'password' => Hash::make('password'),
                'phone' => '+336' . str_pad(rand(10000000, 99999999), 8, '0', STR_PAD_LEFT),
                'birthdate' => Carbon::parse("$birthYear-$birthMonth-$birthDay"),
                'city' => $cities[array_rand($cities)],
                'role' => 'supervisor',
                'bio' => 'Superviseur expérimenté dans la gestion de campagnes marketing avec influenceurs.',
                'is_active' => $i <= 5, // 5 sur 6 sont actifs
            ]);
        }

        // Création de 20 influenceurs (16 actifs, 4 inactifs)
        $names = [
            'Sophie Martin', 'Thomas Bernard', 'Emma Dubois', 'Lucas Petit', 'Chloé Leroy',
            'Hugo Moreau', 'Camille Roux', 'Nathan Fournier', 'Manon Girard', 'Enzo Bonnet',
            'Léa Lambert', 'Louis Fontaine', 'Julie Rousseau', 'Théo Mercier', 'Léna Vincent',
            'Maxime Durand', 'Jade Lefevre', 'Adam Gauthier', 'Alice Perrin', 'Gabriel Morel'
        ];
        
        // Pseudos pour les influenceurs
        $influencerPseudos = [
            'sophie_lifestyle', 'tom_fashion', 'emma_beauty', 'lucas_tech', 'chloe_food',
            'hugo_travel', 'cam_style', 'nathan_gaming', 'manon_deco', 'enzo_sport',
            'lea_mode', 'louis_photo', 'julie_cuisine', 'theo_voyage', 'lena_diy',
            'max_fitness', 'jade_makeup', 'adam_geek', 'alice_books', 'gab_music'
        ];
        
        // Biographies prédéfinies pour les influenceurs
        $bios = [
            'Influenceur lifestyle et voyage. Passionné par la découverte de nouvelles cultures.',
            'Créateur de contenu spécialisé dans la mode et les tendances actuelles.',
            'Expert en beauté et bien-être. Partage des conseils et astuces quotidiennement.',
            'Passionné de technologie et d\'innovation. Teste et présente les derniers gadgets.',
            'Influenceur food et cuisine. Partage des recettes et découvertes culinaires.',
        ];
        
        // Plateformes sociales disponibles
        $platforms = ['instagram', 'twitter', 'facebook', 'youtube', 'tiktok', 'linkedin'];
        
        for ($i = 1; $i <= 20; $i++) {
            $socialLinks = [];
            
            // Générer des liens sociaux de manière déterministe
            // Chaque influenceur aura entre 2 et 5 réseaux sociaux
            foreach ($platforms as $index => $platform) {
                if ($index % 6 < 5 || $i % 3 == 0) {
                    $socialLinks[$platform] = 'https://' . $platform . '.com/' . $influencerPseudos[$i-1];
                }
            }
            
            $birthYear = rand(1990, 2000);
            $birthMonth = rand(1, 12);
            $birthDay = rand(1, 28);
            
            User::create([
                'name' => $names[$i-1],
                'pseudo' => $influencerPseudos[$i-1],
                'email' => 'influencer' . $i . '@influboss.com',
                'password' => Hash::make('password'),
                'phone' => '+336' . str_pad(rand(10000000, 99999999), 8, '0', STR_PAD_LEFT),
                'birthdate' => Carbon::parse("$birthYear-$birthMonth-$birthDay"),
                'city' => $cities[array_rand($cities)],
                'role' => 'influencer',
                'bio' => $bios[$i % 5],
                'social_links' => json_encode($socialLinks),
                'is_active' => ($i <= 16), // 16 sur 20 sont actifs
            ]);
        }
    }
}