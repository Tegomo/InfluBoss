<?php

namespace App\Http\Controllers;

use App\Exports\GlobalDataExport;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Facades\Excel;

class GlobalExportController extends Controller
{
    /**
     * Exporte toutes les données du site en Excel
     */
    public function exportAll()
    {
        // Vérifier que l'utilisateur est admin ou super_admin
        if (!in_array(Auth::user()->role, ['admin', 'super_admin'])) {
            return redirect()->back()->withErrors(['error' => 'Vous n\'avez pas l\'autorisation d\'exporter toutes les données.']);
        }

        // Générer le nom du fichier avec la date
        $filename = 'influboss_export_complet_' . now()->format('Y-m-d_His') . '.xlsx';
        
        return Excel::download(new GlobalDataExport(Auth::user()), $filename);
    }
}
