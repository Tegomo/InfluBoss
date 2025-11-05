<?php

namespace App\Exports;

use App\Models\Stat;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class StatsSheet implements FromCollection, WithTitle, WithHeadings, WithMapping, WithStyles
{
    protected $user;

    public function __construct($user = null)
    {
        $this->user = $user;
    }

    public function collection()
    {
        return Stat::with(['publication.influencer', 'publication.assignment.campaign'])->get();
    }

    public function map($stat): array
    {
        $totalEngagement = $stat->likes + $stat->comments + $stat->shares + $stat->saves;
        
        return [
            $stat->id,
            $stat->publication_id,
            $stat->publication->influencer->name ?? 'N/A',
            $stat->publication->assignment->campaign->name ?? 'N/A',
            $stat->publication->platform ?? 'N/A',
            $stat->likes,
            $stat->comments,
            $stat->shares,
            $stat->saves,
            $totalEngagement,
            $stat->views,
            $stat->clicks,
            $stat->engagement_rate,
            $stat->reach,
            $stat->impressions,
            $stat->collected_at ? $stat->collected_at->format('d/m/Y H:i') : '',
            $stat->created_at->format('d/m/Y H:i'),
        ];
    }

    public function headings(): array
    {
        return [
            'ID',
            'ID Publication',
            'Influenceur',
            'Campagne',
            'Plateforme',
            'Likes',
            'Commentaires',
            'Partages',
            'Sauvegardes',
            'Engagement total',
            'Vues',
            'Clics',
            'Taux d\'engagement (%)',
            'Portée',
            'Impressions',
            'Date de collecte',
            'Date de création',
        ];
    }

    public function title(): string
    {
        return 'Statistiques';
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
}
