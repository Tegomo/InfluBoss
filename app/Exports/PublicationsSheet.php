<?php

namespace App\Exports;

use App\Models\Publication;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class PublicationsSheet implements FromCollection, WithTitle, WithHeadings, WithMapping, WithStyles
{
    protected $user;

    public function __construct($user = null)
    {
        $this->user = $user;
    }

    public function collection()
    {
        return Publication::with(['influencer', 'assignment.campaign', 'content', 'verifier', 'latestStats'])->get();
    }

    public function map($publication): array
    {
        return [
            $publication->id,
            $publication->influencer->name ?? 'N/A',
            $publication->influencer->email ?? 'N/A',
            $publication->assignment->campaign->name ?? 'N/A',
            ucfirst($publication->platform),
            ucfirst($publication->post_type),
            $publication->post_url,
            $this->getStatusLabel($publication->status),
            $publication->latestStats->likes ?? 0,
            $publication->latestStats->comments ?? 0,
            $publication->latestStats->shares ?? 0,
            $publication->latestStats->views ?? 0,
            $publication->latestStats->engagement_rate ?? 0,
            $publication->published_at ? $publication->published_at->format('d/m/Y H:i') : 'N/A',
            $publication->verified_at ? $publication->verified_at->format('d/m/Y H:i') : 'N/A',
            $publication->verifier->name ?? 'N/A',
            $publication->post_content ?? '',
            $publication->rejection_reason ?? '',
            $publication->created_at->format('d/m/Y H:i'),
        ];
    }

    public function headings(): array
    {
        return [
            'ID',
            'Influenceur',
            'Email Influenceur',
            'Campagne',
            'Plateforme',
            'Type de publication',
            'URL',
            'Statut',
            'Likes',
            'Commentaires',
            'Partages',
            'Vues',
            'Taux d\'engagement (%)',
            'Date de publication',
            'Date de vérification',
            'Vérifié par',
            'Contenu',
            'Raison de rejet',
            'Date de création',
        ];
    }

    public function title(): string
    {
        return 'Publications';
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

    private function getStatusLabel($status)
    {
        switch ($status) {
            case 'pending':
                return 'En attente';
            case 'verified':
                return 'Vérifiée';
            case 'rejected':
                return 'Rejetée';
            default:
                return $status;
        }
    }
}
