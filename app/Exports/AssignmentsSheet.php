<?php

namespace App\Exports;

use App\Models\Assignment;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class AssignmentsSheet implements FromCollection, WithTitle, WithHeadings, WithMapping, WithStyles
{
    protected $user;

    public function __construct($user = null)
    {
        $this->user = $user;
    }

    public function collection()
    {
        return Assignment::with(['campaign', 'influencer', 'supervisor', 'publications'])->get();
    }

    public function map($assignment): array
    {
        $platforms = is_array($assignment->platforms) ? implode(', ', $assignment->platforms) : '';
        $verifiedPosts = $assignment->publications->where('status', 'verified')->count();
        $requiredPosts = $assignment->required_posts ?? 0;
        $completionRate = $requiredPosts > 0 ? round(($verifiedPosts / $requiredPosts) * 100, 2) : 0;
        
        return [
            $assignment->id,
            $assignment->campaign->name ?? '',
            $assignment->influencer->name ?? '',
            $assignment->influencer->email ?? '',
            $assignment->supervisor->name ?? '',
            $this->getStatusLabel($assignment->status),
            $assignment->description ?? '',
            $assignment->instructions ?? '',
            $platforms,
            $requiredPosts,
            $verifiedPosts,
            $completionRate . '%',
            $assignment->compensation ?? 0,
            $assignment->deadline ? $assignment->deadline->format('d/m/Y') : '',
            $assignment->assigned_at ? $assignment->assigned_at->format('d/m/Y H:i') : '',
            $assignment->accepted_at ? $assignment->accepted_at->format('d/m/Y H:i') : '',
            $assignment->created_at->format('d/m/Y H:i'),
        ];
    }

    public function headings(): array
    {
        return [
            'ID',
            'Campagne',
            'Influenceur',
            'Email Influenceur',
            'Superviseur',
            'Statut',
            'Description',
            'Instructions',
            'Plateformes',
            'Publications requises',
            'Publications vérifiées',
            'Taux de complétion',
            'Compensation',
            'Date limite',
            'Date d\'assignation',
            'Date d\'acceptation',
            'Date de création',
        ];
    }

    public function title(): string
    {
        return 'Missions';
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
            'pending' => 'En attente',
            'accepted' => 'Acceptée',
            'declined' => 'Refusée',
            'in_progress' => 'En cours',
            'completed' => 'Terminée',
        ];
        return $statuses[$status] ?? $status;
    }
}
