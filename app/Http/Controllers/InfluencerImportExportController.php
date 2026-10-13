<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Services\SocialAccountService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Import et export CSV des influenceurs (admins et super_admins uniquement).
 *
 * Colonnes d'import : nom, pseudo, email, telephone, adresse, ville, categories
 * (clés ou libellés séparés par « | »), une colonne par plateforme (URL ou @handle)
 * et une colonne « {plateforme}_abonnes », puis actif (Oui/Non).
 */
class InfluencerImportExportController extends Controller
{
    private const FOLLOWERS_SUFFIX = '_abonnes';

    public function __construct(private SocialAccountService $socialAccounts) {}

    /**
     * Affiche la page d'import/export des influenceurs
     */
    public function index()
    {
        $this->authorizeAdmin();

        return view('influencers.import-export', [
            'influencersCount' => User::where('role', 'influencer')->count(),
            'categories' => config('influboss.categories'),
            'platforms' => config('influboss.platforms'),
        ]);
    }

    /**
     * Exporte les influenceurs au format CSV
     */
    public function export()
    {
        $this->authorizeAdmin();

        $platforms = config('influboss.platforms');

        $headers = ['ID', 'Nom', 'Pseudo', 'Email', 'Téléphone', 'Adresse', 'Ville', 'Catégories'];
        foreach ($platforms as $cfg) {
            $headers[] = $cfg['label'].' (URL)';
            $headers[] = $cfg['label'].' (abonnés)';
        }
        $headers[] = 'Actif';
        $headers[] = 'Date de création';

        $callback = function () use ($headers, $platforms) {
            $file = fopen('php://output', 'w');
            fwrite($file, "\xEF\xBB\xBF"); // BOM UTF-8 pour Excel
            fputcsv($file, $headers);

            User::where('role', 'influencer')
                ->with('socialAccounts')
                ->orderBy('name')
                ->chunk(200, function ($users) use ($file, $platforms) {
                    foreach ($users as $user) {
                        $accounts = $user->socialAccounts->keyBy('platform');
                        $row = [
                            $user->id,
                            $user->name,
                            $user->pseudo ?? '',
                            $user->email,
                            $user->phone ?? '',
                            $user->address ?? '',
                            $user->city ?? '',
                            implode('|', $user->categories ?? []),
                        ];
                        foreach (array_keys($platforms) as $platform) {
                            $account = $accounts[$platform] ?? null;
                            $row[] = $account?->url ?? '';
                            $row[] = $account?->followers_count ?? '';
                        }
                        $row[] = $user->is_active ? 'Oui' : 'Non';
                        $row[] = $user->created_at->format('d/m/Y');

                        fputcsv($file, $row);
                    }
                });

            fclose($file);
        };

        return new StreamedResponse($callback, 200, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="influenceurs_'.date('Y-m-d').'.csv"',
        ]);
    }

    /**
     * Télécharger le modèle d'import CSV
     */
    public function downloadTemplate()
    {
        $this->authorizeAdmin();

        $callback = function () {
            $file = fopen('php://output', 'w');
            fwrite($file, "\xEF\xBB\xBF");
            fputcsv($file, $this->templateHeaders());
            fputcsv($file, $this->templateExampleRow());
            fclose($file);
        };

        return new StreamedResponse($callback, 200, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="modele_import_influenceurs.csv"',
        ]);
    }

    /**
     * Importe les influenceurs depuis un fichier CSV
     */
    public function import(Request $request)
    {
        $this->authorizeAdmin();

        $validator = Validator::make($request->all(), [
            'file' => 'required|mimes:csv,txt|max:2048',
        ], [
            'file.required' => 'Veuillez sélectionner un fichier à importer',
            'file.mimes' => 'Le fichier doit être au format CSV (.csv)',
            'file.max' => 'La taille du fichier ne doit pas dépasser 2 Mo',
        ]);

        if ($validator->fails()) {
            return redirect()->route('influencers.import-export')->withErrors($validator)->withInput();
        }

        try {
            [$headers, $rows] = $this->readCsv($request->file('file')->getRealPath());

            foreach (['nom', 'email'] as $required) {
                if (! in_array($required, $headers, true)) {
                    return redirect()->route('influencers.import-export')
                        ->withErrors(['error' => 'Le fichier CSV ne contient pas les colonnes requises (nom, email).']);
                }
            }

            [$prepared, $errors] = $this->prepareRows($rows);

            if ($errors !== []) {
                return redirect()->route('influencers.import-export')
                    ->withErrors(['error' => 'Erreurs dans le fichier CSV :', 'errors' => $errors]);
            }

            DB::transaction(function () use ($prepared) {
                foreach ($prepared as $row) {
                    $user = User::create($row['user']);
                    $this->socialAccounts->syncForUser($user, $row['social'], Auth::id());
                }
            });

            $count = count($prepared);

            return redirect()->route('influencers.import-export')
                ->with('success', "{$count} ambassadeur(s) importé(s) avec succès.");
        } catch (\Throwable $e) {
            return redirect()->route('influencers.import-export')
                ->withErrors(['error' => 'Une erreur est survenue lors de l\'import : '.$e->getMessage()]);
        }
    }

    private function authorizeAdmin(): void
    {
        if (! in_array(Auth::user()->role, ['admin', 'super_admin'])) {
            abort(403, 'Action non autorisée.');
        }
    }

    /**
     * Lit le CSV et renvoie [en-têtes normalisées, lignes indexées par en-tête].
     *
     * @return array{0: array<int, string>, 1: array<int, array<string, string>>}
     */
    private function readCsv(string $path): array
    {
        $handle = fopen($path, 'r');
        $headers = fgetcsv($handle) ?: [];
        $headers = array_map(fn ($h) => strtolower(trim(str_replace("\xEF\xBB\xBF", '', (string) $h))), $headers);

        $rows = [];
        while (($data = fgetcsv($handle)) !== false) {
            if ($data === [null] || $data === ['']) {
                continue; // ligne vide
            }
            $data = array_pad(array_slice($data, 0, count($headers)), count($headers), '');
            $rows[] = array_map(fn ($v) => trim((string) $v), array_combine($headers, $data));
        }
        fclose($handle);

        return [$headers, $rows];
    }

    /**
     * Valide chaque ligne et prépare les attributs utilisateur et comptes sociaux.
     *
     * @return array{0: array<int, array{user: array<string, mixed>, social: array<string, array{url: string, followers: string}>}>, 1: array<int, string>}
     */
    private function prepareRows(array $rows): array
    {
        $platforms = config('influboss.platforms');
        $prepared = [];
        $errors = [];
        $emailsInFile = [];
        $line = 1;

        foreach ($rows as $row) {
            $line++;

            if (($row['nom'] ?? '') === '') {
                $errors[] = "Ligne {$line} : le nom est obligatoire.";
            }

            $email = $row['email'] ?? '';
            if ($email === '') {
                $errors[] = "Ligne {$line} : l'email est obligatoire.";
            } elseif (! filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $errors[] = "Ligne {$line} : l'email « {$email} » est invalide.";
            } elseif (in_array(strtolower($email), $emailsInFile, true)) {
                $errors[] = "Ligne {$line} : l'email « {$email} » apparaît plusieurs fois dans le fichier.";
            } else {
                $emailsInFile[] = strtolower($email);
            }

            $categories = $this->parseCategories($row['categories'] ?? '');
            foreach ($categories['unknown'] as $unknown) {
                $errors[] = "Ligne {$line} : catégorie inconnue « {$unknown} ».";
            }

            $social = [];
            foreach ($this->platformColumns() as $column => $platform) {
                $value = $row[$column] ?? '';
                $followers = $row[$column.self::FOLLOWERS_SUFFIX] ?? '';
                $label = $platforms[$platform]['label'];

                if ($followers !== '' && ! ctype_digit($followers)) {
                    $errors[] = "Ligne {$line} : le nombre d'abonnés {$label} doit être un entier positif.";
                }
                if ($followers !== '' && $value === '') {
                    $errors[] = "Ligne {$line} : un nombre d'abonnés {$label} est indiqué sans compte {$label}.";
                }
                if ($value !== '' && ! isset($social[$platform])) {
                    $social[$platform] = ['url' => $value, 'followers' => $followers];
                }
            }

            $prepared[] = [
                'user' => [
                    'name' => $row['nom'] ?? '',
                    'pseudo' => ($row['pseudo'] ?? '') !== '' ? $row['pseudo'] : null,
                    'email' => $email,
                    'password' => Hash::make(Str::random(12)),
                    'role' => 'influencer',
                    'phone' => ($row['telephone'] ?? '') !== '' ? $row['telephone'] : null,
                    'address' => ($row['adresse'] ?? '') !== '' ? $row['adresse'] : null,
                    'city' => ($row['ville'] ?? '') !== '' ? $row['ville'] : null,
                    'categories' => $categories['keys'],
                    'is_active' => strtolower($row['actif'] ?? '') === 'oui',
                ],
                'social' => $social,
            ];
        }

        $existing = User::withTrashed()->whereIn('email', $emailsInFile)->pluck('email');
        foreach ($existing as $email) {
            $errors[] = "L'email « {$email} » est déjà utilisé par un autre utilisateur.";
        }

        return [$prepared, $errors];
    }

    /**
     * Convertit « Tech|humour » en clés de config ; accepte les clés ou les libellés, sans tenir compte de la casse.
     *
     * @return array{keys: array<int, string>, unknown: array<int, string>}
     */
    private function parseCategories(string $raw): array
    {
        $labels = config('influboss.categories');
        $byLabel = [];
        foreach ($labels as $key => $label) {
            $byLabel[mb_strtolower($label)] = $key;
        }

        $keys = [];
        $unknown = [];
        foreach (explode('|', $raw) as $segment) {
            $segment = trim($segment);
            if ($segment === '') {
                continue;
            }
            $lower = mb_strtolower($segment);
            if (isset($labels[$lower])) {
                $keys[] = $lower;
            } elseif (isset($byLabel[$lower])) {
                $keys[] = $byLabel[$lower];
            } else {
                $unknown[] = $segment;
            }
        }

        return ['keys' => array_values(array_unique($keys)), 'unknown' => $unknown];
    }

    /**
     * Colonnes CSV acceptées pour les comptes sociaux (clé actuelle et alias historiques) → plateforme.
     *
     * @return array<string, string>
     */
    private function platformColumns(): array
    {
        $columns = [];
        foreach (array_keys(config('influboss.platforms')) as $key) {
            $columns[$key] = $key;
        }
        foreach (config('influboss.legacy_platform_keys', []) as $legacy => $key) {
            $columns[$legacy] = $key;
        }

        return $columns;
    }

    /**
     * @return array<int, string>
     */
    private function templateHeaders(): array
    {
        $headers = ['nom', 'pseudo', 'email', 'telephone', 'adresse', 'ville', 'categories'];
        foreach (array_keys(config('influboss.platforms')) as $key) {
            $headers[] = $key;
            $headers[] = $key.self::FOLLOWERS_SUFFIX;
        }
        $headers[] = 'actif';

        return $headers;
    }

    /**
     * @return array<int, string>
     */
    private function templateExampleRow(): array
    {
        $row = ['Nom Ambassadeur', 'pseudo_ambassadeur', 'email@exemple.com', '+237600000000', 'Akwa, Douala', 'Douala', 'lifestyle|tech'];
        foreach (config('influboss.platforms') as $cfg) {
            $row[] = $cfg['url_prefix'].'pseudo_ambassadeur';
            $row[] = '12000';
        }
        $row[] = 'Oui';

        return $row;
    }
}
