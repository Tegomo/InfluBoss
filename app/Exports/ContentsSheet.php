<?php

namespace App\Exports;

use App\Models\Content;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class ContentsSheet implements FromCollection, WithTitle, WithHeadings, WithMapping, WithStyles
{
    protected $user;

    public function __construct($user = null)
    {
        $this->user = $user;
    }

    public function collection()
    {
        return Content::with(['campaign', 'creator', 'publications'])->get();
    }

    public function map($content): array
    {
        $guidelines = is_array($content->guidelines) ? implode(', ', $content->guidelines) : $content->guidelines;
        
        return [
            $content->id,
            $content->title,
            $content->description ?? '',
            $content->campaign->name ?? '',
            $this->getTypeLabel($content->type),
            $this->getStatusLabel($content->status),
            $content->file_path ?? '',
            $content->content_text ?? '',
            $guidelines ?? '',
            $content->tags ?? '',
            $content->publication_date ? $content->publication_date->format('d/m/Y') : '',
            $content->creator->name ?? '',
            $content->publications->count(),
            $content->created_at->format('d/m/Y H:i'),
            $content->updated_at->format('d/m/Y H:i'),
        ];
    }

    public function headings(): array
    {
        return [
            'ID',
            'Titre',
            'Description',
            'Campagne',
            'Type',
            'Statut',
            'Chemin du fichier',
            'Contenu texte',
            'Guidelines',
            'Tags',
            'Date de publication',
            'Créé par',
            'Nombre d\'utilisations',
            'Date de création',
            'Dernière modification',
        ];
    }

    public function title(): string
    {
        return 'Contenus';
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

    private function getTypeLabel($type)
    {
        $types = [
            'image' => 'Image',
            'video' => 'Vidéo',
            'text' => 'Texte',
            'link' => 'Lien',
        ];
        return $types[$type] ?? $type;
    }

    private function getStatusLabel($status)
    {
        $statuses = [
            'draft' => 'Brouillon',
            'approved' => 'Approuvé',
            'archived' => 'Archivé',
        ];
        return $statuses[$status] ?? $status;
    }
}
