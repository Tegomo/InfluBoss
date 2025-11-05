<?php

namespace App\Exports;

use App\Models\Note;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class NotesSheet implements FromCollection, WithTitle, WithHeadings, WithMapping, WithStyles
{
    protected $user;

    public function __construct($user = null)
    {
        $this->user = $user;
    }

    public function collection()
    {
        return Note::with(['assignment.campaign', 'assignment.influencer', 'user'])->get();
    }

    public function map($note): array
    {
        return [
            $note->id,
            $note->assignment_id,
            $note->assignment->campaign->name ?? 'N/A',
            $note->assignment->influencer->name ?? 'N/A',
            $note->user->name ?? 'N/A',
            $note->user->role ?? 'N/A',
            $note->content,
            $note->created_at->format('d/m/Y H:i'),
            $note->updated_at->format('d/m/Y H:i'),
        ];
    }

    public function headings(): array
    {
        return [
            'ID',
            'ID Mission',
            'Campagne',
            'Influenceur',
            'Auteur',
            'Rôle auteur',
            'Contenu',
            'Date de création',
            'Dernière modification',
        ];
    }

    public function title(): string
    {
        return 'Notes';
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
