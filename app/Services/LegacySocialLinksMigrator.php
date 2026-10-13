<?php

namespace App\Services;

use App\Models\SocialAccount;
use Illuminate\Support\Facades\DB;

/**
 * Copie les anciens liens sociaux (users.social_links et colonnes *_handle)
 * vers la table social_accounts. Idempotent : un compte déjà présent pour
 * (utilisateur, plateforme) est laissé tel quel. Les anciennes colonnes ne
 * sont jamais modifiées.
 */
final class LegacySocialLinksMigrator
{
    /**
     * @return array{created: int, skipped: int}
     */
    public function migrate(): array
    {
        $handleColumns = config('influboss.legacy_handle_columns', []);
        $created = 0;
        $skipped = 0;

        DB::table('users')
            ->select(array_merge(['id', 'social_links'], array_keys($handleColumns)))
            ->orderBy('id')
            ->chunkById(200, function ($users) use ($handleColumns, &$created, &$skipped) {
                foreach ($users as $user) {
                    foreach ($this->legacyValuesFor($user, $handleColumns) as $platform => $value) {
                        $exists = DB::table('social_accounts')
                            ->where('user_id', $user->id)
                            ->where('platform', $platform)
                            ->exists();

                        if ($exists) {
                            $skipped++;

                            continue;
                        }

                        $parsed = SocialAccount::parseInput($platform, $value);
                        $now = now();

                        DB::table('social_accounts')->insert([
                            'user_id' => $user->id,
                            'platform' => $platform,
                            'url' => $parsed['url'],
                            'handle' => $parsed['handle'],
                            'followers_count' => null,
                            'last_synced_at' => null,
                            'created_at' => $now,
                            'updated_at' => $now,
                        ]);
                        $created++;
                    }
                }
            });

        return ['created' => $created, 'skipped' => $skipped];
    }

    /**
     * Valeurs legacy d'un utilisateur, indexées par plateforme actuelle.
     * social_links prime sur les colonnes *_handle.
     *
     * @return array<string, string>
     */
    private function legacyValuesFor(object $user, array $handleColumns): array
    {
        $values = [];

        foreach ($this->decodeSocialLinks($user->social_links) as $key => $value) {
            $platform = $this->normalizePlatform((string) $key);
            if ($platform !== null && $this->isUsable($value) && ! isset($values[$platform])) {
                $values[$platform] = trim($value);
            }
        }

        foreach ($handleColumns as $column => $platform) {
            $value = $user->{$column} ?? null;
            if ($this->isUsable($value) && ! isset($values[$platform])) {
                $values[$platform] = trim($value);
            }
        }

        return $values;
    }

    /**
     * Décode social_links en tolérant un JSON encodé plusieurs fois.
     */
    private function decodeSocialLinks(mixed $raw): array
    {
        $decoded = $raw;
        for ($i = 0; $i < 3 && is_string($decoded); $i++) {
            $decoded = json_decode($decoded, true);
        }

        return is_array($decoded) ? $decoded : [];
    }

    private function normalizePlatform(string $key): ?string
    {
        $key = strtolower(trim($key));
        $key = config('influboss.legacy_platform_keys')[$key] ?? $key;

        return array_key_exists($key, config('influboss.platforms', [])) ? $key : null;
    }

    private function isUsable(mixed $value): bool
    {
        return is_string($value) && trim($value) !== '';
    }
}
