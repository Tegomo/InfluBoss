<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Symfony\Component\HttpFoundation\StreamedResponse;

class InfluencerImportExportController extends Controller
{
    /**
     * Affiche la page d'import/export des influenceurs
     */
    public function index()
    {
        // Vérifier les autorisations (seuls les admin et super_admin peuvent gérer les imports/exports)
        if (!in_array(\Illuminate\Support\Facades\Auth::user()->role, ['admin', 'super_admin'])) {
            abort(403, 'Action non autorisée.');
        }
        
        $influencersCount = User::where('role', 'influencer')->count();
        
        return view('influencers.import-export', compact('influencersCount'));
    }

    /**
     * Exporte les influenceurs au format CSV
     */
    public function export() 
    {
        // Vérifier les autorisations (seuls les admin et super_admin peuvent gérer les imports/exports)
        if (!in_array(Auth::user()->role, ['admin', 'super_admin'])) {
            abort(403, 'Action non autorisée.');
        }
        
        $fileName = 'influenceurs_'.date('Y-m-d').'.csv';
        
        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="'.$fileName.'"',
        ];
        
        // Créer un fichier CSV à la volée
        $callback = function() {
            $file = fopen('php://output', 'w');
            
            // Ajouter le BOM UTF-8 pour l'encodage correct dans Excel
            fputs($file, "\xEF\xBB\xBF");
            
            // En-têtes du CSV
            fputcsv($file, [
                'ID', 'Nom', 'Pseudo', 'Email', 'Téléphone', 'Date de naissance', 'Ville', 'Instagram', 'Facebook', 'YouTube', 
                'TikTok', 'Twitter', 'LinkedIn', 'Actif', 'Date de création'
            ]);
            
            // Données
            $users = User::where('role', 'influencer')->get();
            foreach ($users as $user) {
                fputcsv($file, [
                    $user->id,
                    $user->name,
                    $user->pseudo ?? '',
                    $user->email,
                    $user->phone ?? '',
                    $user->birthdate ? $user->birthdate->format('d/m/Y') : '',
                    $user->city ?? '',
                    $user->instagram_handle ?? '',
                    $user->facebook_handle ?? '',
                    $user->youtube_handle ?? '',
                    $user->tiktok_handle ?? '',
                    $user->twitter_handle ?? '',
                    $user->linkedin_handle ?? '',
                    $user->is_active ? 'Oui' : 'Non',
                    $user->created_at->format('d/m/Y'),
                ]);
            }
            
            fclose($file);
        };
        
        return new StreamedResponse($callback, 200, $headers);
    }
   
    /**
     * Télécharger le modèle d'import CSV vide
     */
    public function downloadTemplate()
    {
        // Vérifier les autorisations (seuls les admin et super_admin peuvent gérer les imports/exports)
        if (!in_array(Auth::user()->role, ['admin', 'super_admin'])) {
            abort(403, 'Action non autorisée.');
        }
        
        $fileName = 'modele_import_influenceurs.csv';
        
        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="'.$fileName.'"',
        ];
        
        $callback = function() {
            $file = fopen('php://output', 'w');
            
            // Ajouter le BOM UTF-8 pour l'encodage correct dans Excel
            fputs($file, "\xEF\xBB\xBF");
            
            // En-têtes du CSV
            fputcsv($file, [
                'nom', 'pseudo', 'email', 'telephone', 'date_naissance', 'ville', 'instagram', 'facebook', 'youtube', 
                'tiktok', 'twitter', 'linkedin', 'actif'
            ]);
            
            // Une ligne d'exemple (optionnelle)
            fputcsv($file, [
                'Nom Influenceur', 'pseudo_influenceur', 'email@exemple.com', '+33600000000', '15/01/1990', 'Paris', 'instagram_handle',
                'facebook_handle', 'youtube_handle', 'tiktok_handle', 'twitter_handle',
                'linkedin_handle', 'Oui'
            ]);
            
            fclose($file);
        };
        
        return new StreamedResponse($callback, 200, $headers);
    }

    /**
     * Importe les influenceurs depuis un fichier CSV
     */
    public function import(Request $request)
    {
        // Vérifier les autorisations (seuls les admin et super_admin peuvent gérer les imports/exports)
        if (!in_array(Auth::user()->role, ['admin', 'super_admin'])) {
            abort(403, 'Action non autorisée.');
        }
        
        $validator = Validator::make($request->all(), [
            'file' => 'required|mimes:csv,txt|max:2048',
        ], [
            'file.required' => 'Veuillez sélectionner un fichier à importer',
            'file.mimes' => 'Le fichier doit être au format CSV (.csv)',
            'file.max' => 'La taille du fichier ne doit pas dépasser 2 Mo',
        ]);
        
        if ($validator->fails()) {
            return redirect()->route('influencers.import-export')
                ->withErrors($validator)
                ->withInput();
        }

        try {
            $file = $request->file('file');
            $path = $file->getRealPath();
            
            $handle = fopen($path, 'r');
            
            // Sauter la première ligne (en-têtes)
            $headers = fgetcsv($handle);
            
            // Vérifier que les en-têtes requis sont présents
            $requiredHeaders = ['nom', 'email'];
            foreach ($requiredHeaders as $header) {
                if (!in_array($header, $headers)) {
                    return redirect()->route('influencers.import-export')
                        ->withErrors(['error' => 'Le fichier CSV ne contient pas les colonnes requises (nom, email).']);
                }
            }
            
            // Première passe pour valider toutes les données
            $errors = [];
            $line = 2; // Débute à la ligne 2 (après les en-têtes)
            $emailsToCheck = [];
            
            // Revenir au début du fichier et sauter les en-têtes
            rewind($handle);
            fgetcsv($handle);
            
            while (($data = fgetcsv($handle)) !== FALSE) {
                $rowData = array_combine($headers, $data);
                
                // Vérifier que les champs obligatoires sont présents
                if (empty($rowData['nom'])) {
                    $errors[] = "Ligne $line: Le nom est obligatoire.";
                }
                
                if (empty($rowData['email'])) {
                    $errors[] = "Ligne $line: L'email est obligatoire.";
                } elseif (!filter_var($rowData['email'], FILTER_VALIDATE_EMAIL)) {
                    $errors[] = "Ligne $line: L'email '{$rowData['email']}' est invalide.";
                } else {
                    $emailsToCheck[] = $rowData['email'];
                }
                
                $line++;
            }
            
            // Vérifier si les emails existent déjà
            $existingEmails = User::whereIn('email', $emailsToCheck)->pluck('email')->toArray();
            if (!empty($existingEmails)) {
                foreach ($existingEmails as $email) {
                    $errors[] = "L'email '$email' est déjà utilisé par un autre utilisateur.";
                }
            }
            
            // Si des erreurs sont détectées, annuler l'import
            if (!empty($errors)) {
                return redirect()->route('influencers.import-export')
                    ->withErrors(['error' => 'Erreurs dans le fichier CSV:', 'errors' => $errors]);
            }
            
            // Si aucune erreur, procéder à l'import
            rewind($handle);
            fgetcsv($handle); // Sauter les en-têtes
            
            $importedCount = 0;
            while (($data = fgetcsv($handle)) !== FALSE) {
                $rowData = array_combine($headers, $data);
                
                // Génération d'un mot de passe aléatoire
                $temporaryPassword = substr(md5(mt_rand()), 0, 8);
                
                // Traitement de la date de naissance si fournie
                $birthdate = null;
                if (!empty($rowData['date_naissance'])) {
                    try {
                        $birthdate = \Carbon\Carbon::createFromFormat('d/m/Y', $rowData['date_naissance']);
                    } catch (\Exception $e) {
                        // Si le format n'est pas correct, on essaie un autre format courant
                        try {
                            $birthdate = \Carbon\Carbon::parse($rowData['date_naissance']);
                        } catch (\Exception $e) {
                            // Si toujours pas valide, on ignore la date
                            $birthdate = null;
                        }
                    }
                }
                
                // Créer l'utilisateur
                $user = new User([
                    'name' => $rowData['nom'],
                    'pseudo' => $rowData['pseudo'] ?? null,
                    'email' => $rowData['email'],
                    'password' => Hash::make($temporaryPassword),
                    'role' => 'influencer',
                    'phone' => $rowData['telephone'] ?? null,
                    'birthdate' => $birthdate,
                    'city' => $rowData['ville'] ?? null,
                    'instagram_handle' => $rowData['instagram'] ?? null,
                    'facebook_handle' => $rowData['facebook'] ?? null,
                    'youtube_handle' => $rowData['youtube'] ?? null,
                    'tiktok_handle' => $rowData['tiktok'] ?? null,
                    'twitter_handle' => $rowData['twitter'] ?? null,
                    'linkedin_handle' => $rowData['linkedin'] ?? null,
                    'is_active' => isset($rowData['actif']) && strtolower($rowData['actif']) === 'oui' ? true : false,
                ]);
                
                $user->save();
                $importedCount++;
            }
            
            fclose($handle);
            
            return redirect()->route('influencers.import-export')
                ->with('success', "$importedCount influenceurs ont été importés avec succès.");
        } catch (\Exception $e) {
            return redirect()->route('influencers.import-export')
                ->withErrors(['error' => 'Une erreur est survenue lors de l\'import: ' . $e->getMessage()]);
        }
    }
}
