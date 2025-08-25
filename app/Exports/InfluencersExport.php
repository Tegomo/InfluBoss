<?php

namespace App\Exports;

use App\Models\User;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\Exportable;

class InfluencersExport implements FromQuery, WithHeadings, WithMapping
{
    use Exportable;

    /**
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function query()
    {
        return User::query()->where('role', 'influencer');
    }

    /**
     * @var User $user
     */
    public function map($user): array
    {
        return [
            $user->id,
            $user->name,
            $user->email,
            $user->phone ?? '',
            $user->instagram_handle ?? '',
            $user->facebook_handle ?? '',
            $user->youtube_handle ?? '',
            $user->tiktok_handle ?? '',
            $user->twitter_handle ?? '',
            $user->linkedin_handle ?? '',
            $user->is_active ? 'Oui' : 'Non',
            $user->created_at->format('d/m/Y'),
        ];
    }

    public function headings(): array
    {
        return [
            'ID',
            'Nom',
            'Email',
            'Téléphone',
            'Instagram',
            'Facebook',
            'YouTube',
            'TikTok',
            'Twitter',
            'LinkedIn',
            'Actif',
            'Date de création',
        ];
    }
}
