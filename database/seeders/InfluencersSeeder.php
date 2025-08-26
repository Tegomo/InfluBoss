<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class InfluencersSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $influencers = [
            [
                'last_name' => 'PALEJUI BAUGIHO',
                'first_name' => 'SORELLE',
                'pseudo' => 'Sorelle palejui',
                'phone' => '657858767'
            ],
            [
                'last_name' => 'ONANA MBIDA',
                'first_name' => 'Boris Urbain',
                'pseudo' => 'Ombu musik',
                'phone' => '691954530'
            ],
            [
                'last_name' => 'DZOKOU',
                'first_name' => 'Fabrice',
                'pseudo' => 'Duplexe',
                'phone' => '655432391'
            ],
            [
                'last_name' => 'DAGALAK GAYDAY',
                'first_name' => 'Isabelle',
                'pseudo' => 'Isabelle Gayday',
                'phone' => '698203061'
            ],
            [
                'last_name' => 'MEPOND DOMBEU',
                'first_name' => 'Wulis',
                'pseudo' => 'Wully',
                'phone' => '692236474'
            ],
            [
                'last_name' => 'EPILE',
                'first_name' => 'Ange Julien',
                'pseudo' => 'Chinois',
                'phone' => '658019815'
            ],
            [
                'last_name' => 'GAMY',
                'first_name' => 'Merline',
                'pseudo' => 'Rose',
                'phone' => '691768625'
            ],
            [
                'last_name' => 'BELOMO',
                'first_name' => 'Emmanuel Axel',
                'pseudo' => 'Axel Emmanuel',
                'phone' => '658496969'
            ],
            [
                'last_name' => 'HAMID',
                'first_name' => 'Charo',
                'pseudo' => 'Charo',
                'phone' => '696916506'
            ],
            [
                'last_name' => 'DJONG YANG',
                'first_name' => 'FERDINAND',
                'pseudo' => 'DY FERDINAND',
                'phone' => '237691000000'
            ],
            [
                'last_name' => 'OWONO MONGO',
                'first_name' => 'Serges',
                'pseudo' => 'Profdunet',
                'phone' => '696737732'
            ],
            [
                'last_name' => 'POPBOU TCHINDA',
                'first_name' => 'Camille',
                'pseudo' => 'Camille Popbou',
                'phone' => '699391012'
            ],
            [
                'last_name' => 'IBRAHIM SADAM',
                'first_name' => 'Moussa',
                'pseudo' => 'Charifa queen bleu',
                'phone' => '697656559'
            ],
            [
                'last_name' => 'MBANGO MBANGO',
                'first_name' => 'Eitel',
                'pseudo' => 'Eitel MBANGO',
                'phone' => '694579003'
            ],
            [
                'last_name' => 'NGNOTSO DZUBU',
                'first_name' => 'Hyppolyte',
                'pseudo' => 'Fils Bio',
                'phone' => '693102068'
            ],
            [
                'last_name' => 'MAWOUBA DJANATOU',
                'first_name' => 'Diane',
                'pseudo' => 'Diane Mawouba officiel',
                'phone' => '699729220'
            ],
            [
                'last_name' => 'SALLA',
                'first_name' => 'Duval',
                'pseudo' => 'Duvalsalla',
                'phone' => '690800015'
            ],
            [
                'last_name' => 'HADJA DIDJA HAMADOU',
                'first_name' => 'Khadidja',
                'pseudo' => 'Khadidja',
                'phone' => '697845230'
            ],
            [
                'last_name' => 'BIYONG',
                'first_name' => 'Arsène Thibaut',
                'pseudo' => 'Arsène B',
                'phone' => '690553924'
            ],
            [
                'last_name' => 'SOMO',
                'first_name' => 'Simon Pierre',
                'pseudo' => 'J-zeus',
                'phone' => '690139342'
            ],
            [
                'last_name' => 'FAME',
                'first_name' => 'Majolie',
                'pseudo' => 'Camer237',
                'phone' => '693941594'
            ],
            [
                'last_name' => 'NZALLI MAMBOU',
                'first_name' => 'Freddy',
                'pseudo' => 'Nzalli Freddy',
                'phone' => '691846981'
            ],
            [
                'last_name' => 'KANGALDEY',
                'first_name' => 'LIMAN',
                'pseudo' => 'Info sur les chocos',
                'phone' => '698744993'
            ],
            [
                'last_name' => 'TAFO FONGANG',
                'first_name' => 'Richard Le Doux',
                'pseudo' => 'Richard Tafo Fongang',
                'phone' => '698268517'
            ],
            [
                'last_name' => 'MVOGO',
                'first_name' => 'Elvis',
                'pseudo' => 'Lekié Express',
                'phone' => '691513704'
            ],
            [
                'last_name' => 'TAGNE KUATE',
                'first_name' => 'WILFRIED STEVE',
                'pseudo' => 'Fomi Steve',
                'phone' => '237700000000'
            ],
            [
                'last_name' => 'NENKAM',
                'first_name' => 'Christian',
                'pseudo' => 'Christian nenkam',
                'phone' => '688409047'
            ],
            [
                'last_name' => 'NGONO ETALI',
                'first_name' => 'Christian Boris',
                'pseudo' => 'Italien',
                'phone' => '691650138'
            ],
            [
                'last_name' => 'JOSEF DAVID',
                'first_name' => 'Von Tchappi',
                'pseudo' => 'Demi lune',
                'phone' => '692934412'
            ],
            [
                'last_name' => 'TOGOTGUE YASIRI',
                'first_name' => 'Maurice',
                'pseudo' => 'Beinnt',
                'phone' => '697836791'
            ],
            [
                'last_name' => 'MBOLLO EFOUBA',
                'first_name' => 'Émérite Samuel',
                'pseudo' => 'Émérite djuna',
                'phone' => '687241983'
            ],
            [
                'last_name' => 'MFOMO KOMO',
                'first_name' => 'Martin Romuald',
                'pseudo' => 'Mr Tchaks',
                'phone' => '695649243'
            ],
            [
                'last_name' => 'ETOUNGOU OWONA',
                'first_name' => 'MICHEL CHRISTIAN',
                'pseudo' => 'MICHAEL',
                'phone' => '699025416'
            ],
            [
                'last_name' => 'YIMELE NJINMOU',
                'first_name' => 'Tibo',
                'pseudo' => 'Tibo Zed',
                'phone' => '694301582'
            ],
            [
                'last_name' => 'NYA',
                'first_name' => 'Leonel',
                'pseudo' => 'Stone',
                'phone' => '693653543'
            ],
            [
                'last_name' => 'MOHAMADOU SAMBO',
                'first_name' => 'Mohamed',
                'pseudo' => 'Mohamed Sambo',
                'phone' => '699300617'
            ]
        ];

        foreach ($influencers as $influencerData) {
            // Générer l'email automatiquement
            $email = $this->generateEmail($influencerData['first_name'], $influencerData['last_name']);
            
            // Créer l'utilisateur influenceur
            User::create([
                'name' => trim($influencerData['first_name'] . ' ' . $influencerData['last_name']),
                'email' => $email,
                'email_verified_at' => now(),
                'password' => Hash::make('Password@2025'),
                'role' => 'influencer',
                'is_active' => true,
                'phone' => $this->cleanPhone($influencerData['phone']),
                'pseudo' => $influencerData['pseudo'],
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
