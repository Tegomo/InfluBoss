<?php

namespace App\Exports;

use App\Models\Campaign;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class CampaignsSheet implements FromCollection, WithTitle, WithHeadings, WithMapping, WithStyles
{
    protected $user;

    public function __construct($user = null)
    {
        $this->user = $user;
    }

    public function collection()
    {
        return Campaign::with(['creator', 'assignments', 'publications'])->get();
    }

    public function map($campaign): array
    {
        $platforms = is_array($campaign->platforms) ? implode(', ', $campaign->platforms) : '';
        $objectives = is_array($campaign->objectives) ? implode(', ', $campaign->objectives) : '';
        
        return [
            $campaign->id,
            $campaign->name,
            $campaign->description ?? '',
            $this->getStatusLabel($campaign->status),
            $campaign->start_date ? $campaign->start_date->format('d/m/Y') : '',
            $campaign->end_date ? $campaign->end_date->format('d/m/Y') : '',
            $platforms,
            $campaign->target_audience ?? '',
            $campaign->budget ?? 0,
            $objectives,
            $campaign->creator->name ?? '',
            $campaign->assignments->count(),
            $campaign->publications->count(),
            $campaign->publications->where('status', 'verified')->count(),
            $campaign->created_at->format('d/m/Y H:i'),
        ];
    }

    public function headings(): array
    {
        return [
            'ID',
            'Nom',
            'Description',
            'Statut',
            'Date de début',
            'Date de fin',
            'Plateformes',
            'Audience cible',
            'Budget',
            'Objectifs',
            'Créé par',
            'Nombre de missions',
            'Nombre de publications',
            'Publications vérifiées',
            'Date de création',
        ];
    }

    public function title(): string
    {
        return 'Campagnes';
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
        $statuses = [
            'draft' => 'Brouillon',
            'active' => 'Active',
            'completed' => 'Terminée',
            'archived' => 'Archivée',
        ];
        return $statuses[$status] ?? $status;
    }
}
