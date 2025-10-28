<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Seed les données dans l'ordre correct pour éviter les problèmes de dépendances
        $this->call([
            UserSeeder::class,                // Crée les utilisateurs (admins)
            SupervisorsSeeder::class,         // Crée les superviseurs
            InfluencersSeeder::class,         // Crée les influenceurs avec données réelles
            //SupervisorInfluencerSeeder::class, // Assigne des influenceurs aux superviseurs
            //CampaignSeeder::class,            // Crée les campagnes marketing
            //ContentSeeder::class,             // Crée les contenus associés aux campagnes
            //AssignmentSeeder::class,          // Assigne des influenceurs aux campagnes
            //PublicationSeeder::class,         // Crée les publications liées aux assignations
        ]);
    }
}
