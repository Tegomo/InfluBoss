<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class SupervisorsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $supervisors = [
            [
                'name' => 'CHOUNFACK Merveille',
                'phone' => '697355977',
            ],
            [
                'name' => 'Bella Mingole',
                'phone' => '696199894',
            ],
            [
                'name' => 'Ifeoma Patricia',
                'phone' => '698081102',
            ],
            [
                'name' => 'Kowa Djiepdo Leonel Richy',
                'phone' => '658775806',
            ],
        ];

        foreach ($supervisors as $supervisorData) {
            // Extraire prénom et nom
            $nameParts = explode(' ', $supervisorData['name'], 2);
            $firstName = $nameParts[0];
            $lastName = isset($nameParts[1]) ? $nameParts[1] : '';
            
            // Générer l'email automatiquement
            $email = $this->generateEmail($firstName, $lastName);
            
            // Créer l'utilisateur superviseur
            User::create([
                'name' => $supervisorData['name'],
                'email' => $email,
                'email_verified_at' => now(),
                'password' => Hash::make('Password@2025'),
                'role' => 'supervisor',
                'is_active' => true,
                'phone' => $this->cleanPhone($supervisorData['phone']),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    /**
     * Génère un email automatiquement basé sur le prénom et nom
     */
    private function generateEmail(string $firstName, string $lastName): string
    {
        // Nettoyer et normaliser les noms
        $firstName = $this->normalizeString($firstName);
        $lastName = $this->normalizeString($lastName);
        
        // Prendre le premier prénom si plusieurs
        $firstNameParts = explode(' ', $firstName);
        $cleanFirstName = strtolower($firstNameParts[0]);
        
        // Prendre le premier nom de famille si plusieurs
        $lastNameParts = explode(' ', $lastName);
        $cleanLastName = strtolower($lastNameParts[0]);
        
        // Générer l'email au format prenom.nom@influboss.cm
        return $cleanFirstName . '.' . $cleanLastName . '@influboss.cm';
    }

    /**
     * Normalise une chaîne en supprimant les accents et caractères spéciaux
     */
    private function normalizeString(string $string): string
    {
        // Supprimer les accents
        $string = iconv('UTF-8', 'ASCII//TRANSLIT', $string);
        
        // Supprimer les caractères spéciaux et garder seulement les lettres et espaces
        $string = preg_replace('/[^a-zA-Z\s]/', '', $string);
        
        // Supprimer les espaces multiples
        $string = preg_replace('/\s+/', ' ', $string);
        
        return trim($string);
    }

    /**
     * Nettoie et formate le numéro de téléphone
     */
    private function cleanPhone(string $phone): string
    {
        // Supprimer tous les caractères non numériques
        $phone = preg_replace('/[^0-9]/', '', $phone);
        
        // Si le numéro commence par 237, le garder tel quel
        if (str_starts_with($phone, '237')) {
            return $phone;
        }
        
        // Si le numéro commence par 6 ou 7 et fait 9 chiffres, ajouter 237
        if ((str_starts_with($phone, '6') || str_starts_with($phone, '7')) && strlen($phone) === 9) {
            return '237' . $phone;
        }
        
        // Sinon, retourner le numéro tel quel
        return $phone;
    }
}
