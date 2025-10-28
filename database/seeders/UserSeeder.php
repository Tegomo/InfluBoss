<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
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
            'password' => Hash::make('Password@2025'),
            'phone' => '+237690000001',
            'birthdate' => Carbon::parse('1985-01-15'),
            'city' => 'Douala',
            'role' => 'super_admin',
            'is_active' => true,
        ]);

        // Administrateur 1
        User::create([
            'name' => 'Admin Orange',
            'pseudo' => 'admin_orange',
            'email' => 'admin.orange@influboss.cm',
            'password' => Hash::make('Password@2025'),
            'phone' => '+237690000002',
            'birthdate' => Carbon::parse('1988-05-20'),
            'city' => 'Yaoundé',
            'role' => 'admin',
            'is_active' => true,
        ]);
        
        // Administrateur 2
        User::create([
            'name' => 'McCANN Douala',
            'pseudo' => 'mccann_douala',
            'email' => 'admin@influboss.cm',
            'password' => Hash::make('Password@2025'),
            'phone' => '+237690000003',
            'birthdate' => Carbon::parse('1990-08-10'),
            'city' => 'Douala',
            'role' => 'admin',
            'is_active' => true,
        ]);
    }
}