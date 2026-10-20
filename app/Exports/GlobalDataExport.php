<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class GlobalDataExport implements WithMultipleSheets
{
    use Exportable;

    protected $user;

    public function __construct($user = null)
    {
        $this->user = $user;
    }

    /**
     * @return array
     */
    public function sheets(): array
    {
        $sheets = [];

        // Feuille 1: Vue d'ensemble
        $sheets[] = new GlobalOverviewSheet($this->user);

        // Feuille 2: Utilisateurs
        $sheets[] = new UsersSheet($this->user);

        // Feuille 3: Campagnes
        $sheets[] = new CampaignsSheet($this->user);

        // Feuille 4: Missions
        $sheets[] = new AssignmentsSheet($this->user);

        // Feuille 5: Publications
        $sheets[] = new PublicationsSheet($this->user);

        // Feuille 6: Contenus
        $sheets[] = new ContentsSheet($this->user);

        // Feuille 7: Statistiques
        $sheets[] = new StatsSheet($this->user);

        // Feuille 8: Notes
        $sheets[] = new NotesSheet($this->user);

        // Feuille 9: Relations Superviseur-Influenceur
        $sheets[] = new SupervisorInfluencerSheet($this->user);

        return $sheets;
    }
}
