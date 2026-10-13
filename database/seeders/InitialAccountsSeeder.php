<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * Comptes de démarrage d'une base vide : un administrateur (super_admin), le compte client Orange en lecture
 * seule et deux superviseurs (liste dans config('influboss.initial_accounts')).
 *
 * Rejouable sans risque : un compte dont l'e-mail existe déjà n'est ni dupliqué ni modifié. Les mots de passe
 * des comptes créés sont générés aléatoirement et affichés une seule fois : les noter puis les faire changer.
 */
class InitialAccountsSeeder extends Seeder
{
    public function run(): void
    {
        $created = $this->createAccounts();

        if (! $this->command) {
            return;
        }

        if ($created === []) {
            $this->command->info('Comptes initiaux déjà présents : rien à créer.');

            return;
        }

        $this->command->warn('Comptes créés. Notez ces mots de passe maintenant : ils ne seront plus affichés.');
        $this->command->table(
            ['Rôle', 'Nom', 'E-mail', 'Mot de passe'],
            array_map(fn ($a) => [$a['role'], $a['name'], $a['email'], $a['password']], $created)
        );
    }

    /**
     * Crée les comptes manquants.
     *
     * @return array<int, array{role: string, name: string, email: string, password: string}>
     */
    public function createAccounts(): array
    {
        $created = [];

        foreach (config('influboss.initial_accounts', []) as $account) {
            if (User::withTrashed()->where('email', $account['email'])->exists()) {
                continue;
            }

            $password = Str::password(16, symbols: false);

            User::create([
                'name' => $account['name'],
                'email' => $account['email'],
                'password' => Hash::make($password),
                'role' => $account['role'],
                'is_active' => true,
            ])->forceFill(['email_verified_at' => now()])->save();

            $created[] = $account + ['password' => $password];
        }

        return $created;
    }
}
