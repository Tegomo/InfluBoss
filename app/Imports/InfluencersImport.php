<?php

namespace App\Imports;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;

class InfluencersImport implements ToModel, WithHeadingRow, WithValidation
{
    /**
     * @param array $row
     *
     * @return \Illuminate\Database\Eloquent\Model|null
     */
    public function model(array $row)
    {
        // Génération d'un mot de passe aléatoire qui sera modifié à la première connexion
        $temporaryPassword = substr(md5(mt_rand()), 0, 8);
        
        return new User([
            'name'            => $row['nom'],
            'email'           => $row['email'],
            'password'        => Hash::make($temporaryPassword),
            'role'            => 'influencer',
            'phone'           => $row['telephone'] ?? null,
            'instagram_handle'=> $row['instagram'] ?? null,
            'facebook_handle' => $row['facebook'] ?? null,
            'youtube_handle'  => $row['youtube'] ?? null,
            'tiktok_handle'   => $row['tiktok'] ?? null,
            'twitter_handle'  => $row['twitter'] ?? null,
            'linkedin_handle' => $row['linkedin'] ?? null,
            'is_active'       => isset($row['actif']) && strtolower($row['actif']) === 'oui' ? true : false,
        ]);
    }

    public function rules(): array
    {
        return [
            'nom'   => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
        ];
    }
    
    public function customValidationMessages()
    {
        return [
            'nom.required' => 'Le champ nom est obligatoire',
            'email.required' => 'Le champ email est obligatoire',
            'email.email' => 'Le format de l\'email est invalide',
            'email.unique' => 'Cet email est déjà utilisé par un autre utilisateur',
        ];
    }
}
