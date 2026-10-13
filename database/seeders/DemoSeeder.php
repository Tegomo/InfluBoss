<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

/**
 * Données de démonstration (développement uniquement) : comptes avec mot de passe connu et influenceurs fictifs.
 * Ne jamais lancer en production. Usage : php artisan db:seed --class=DemoSeeder
 */
class DemoSeeder extends Seeder
{
    public function run(): void
    {
        if (app()->environment('production')) {
            $this->command?->error('DemoSeeder refusé en production.');

            return;
        }

        $this->call([
            UserSeeder::class,
            SupervisorsSeeder::class,
            InfluencersSeeder::class,
        ]);
    }
}
