<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Initialisation d'une base : uniquement les comptes de démarrage (sûr en production).
     * Données de démonstration : php artisan db:seed --class=DemoSeeder
     */
    public function run(): void
    {
        $this->call(InitialAccountsSeeder::class);
    }
}
