<?php

namespace App\Exports;

use App\Models\User;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class UsersSheet implements FromCollection, WithTitle, WithHeadings, WithMapping, WithStyles
{
    protected $user;

    public function __construct($user = null)
    {
        $this->user = $user;
    }

    public function collection()
    {
        return User::with(['supervisedInfluencers', 'supervisors'])->get();
    }

    public function map($user): array
    {
        return [
            $user->id,
            $user->name,
            $user->pseudo ?? '',
            $user->email,
            $this->getRoleLabel($user->role),
            $user->phone ?? '',
            $user->birthdate ? $user->birthdate->format('d/m/Y') : '',
            $user->city ?? '',
            $user->instagram_handle ?? '',
            $user->facebook_handle ?? '',
            $user->youtube_handle ?? '',
            $user->tiktok_handle ?? '',
            $user->twitter_handle ?? '',
            $user->linkedin_handle ?? '',
            $user->is_active ? 'Oui' : 'Non',
            $user->email_verified_at ? 'Oui' : 'Non',
            $user->created_at->format('d/m/Y H:i'),
            $user->updated_at->format('d/m/Y H:i'),
        ];
    }

    public function headings(): array
    {
        return [
            'ID',
            'Nom',
            'Pseudo',
            'Email',
            'Rôle',
            'Téléphone',
            'Date de naissance',
            'Ville',
            'Instagram',
            'Facebook',
            'YouTube',
            'TikTok',
            'Twitter',
            'LinkedIn',
            'Actif',
            'Email vérifié',
            'Date de création',
            'Dernière modification',
        ];
    }

    public function title(): string
    {
        return 'Utilisateurs';
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => [
                'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                'fill' => [
                    'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                    'startColor' => ['rgb' => '17A2B8']
                ],
            ],
        ];
    }

    private function getRoleLabel($role)
    {
        $roles = [
            'influencer' => 'Influenceur',
            'supervisor' => 'Superviseur',
            'admin' => 'Administrateur',
            'super_admin' => 'Super Administrateur',
        ];
        return $roles[$role] ?? $role;
    }
}
