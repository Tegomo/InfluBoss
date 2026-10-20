<?php

namespace App\Exports;

use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class SupervisorInfluencerSheet implements FromCollection, WithTitle, WithHeadings, WithMapping, WithStyles
{
    protected $user;

    public function __construct($user = null)
    {
        $this->user = $user;
    }

    public function collection()
    {
        return DB::table('supervisor_influencer')
            ->join('users as supervisors', 'supervisor_influencer.supervisor_id', '=', 'supervisors.id')
            ->join('users as influencers', 'supervisor_influencer.influencer_id', '=', 'influencers.id')
            ->select(
                'supervisor_influencer.id',
                'supervisor_influencer.supervisor_id',
                'supervisors.name as supervisor_name',
                'supervisors.email as supervisor_email',
                'supervisor_influencer.influencer_id',
                'influencers.name as influencer_name',
                'influencers.email as influencer_email',
                'supervisor_influencer.created_at',
                'supervisor_influencer.updated_at'
            )
            ->get();
    }

    public function map($relation): array
    {
        return [
            $relation->id,
            $relation->supervisor_id,
            $relation->supervisor_name,
            $relation->supervisor_email,
            $relation->influencer_id,
            $relation->influencer_name,
            $relation->influencer_email,
            $relation->created_at ? date('d/m/Y H:i', strtotime($relation->created_at)) : '',
            $relation->updated_at ? date('d/m/Y H:i', strtotime($relation->updated_at)) : '',
        ];
    }

    public function headings(): array
    {
        return [
            'ID',
            'ID Superviseur',
            'Nom Superviseur',
            'Email Superviseur',
            'ID Influenceur',
            'Nom Influenceur',
            'Email Influenceur',
            'Date d\'assignation',
            'Dernière modification',
        ];
    }

    public function title(): string
    {
        return 'Superviseur-Influenceur';
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
