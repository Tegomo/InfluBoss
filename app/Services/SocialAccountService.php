<?php

namespace App\Services;

use App\Models\FollowerSnapshot;
use App\Models\SocialAccount;
use App\Models\User;
use Illuminate\Support\Facades\DB;

/**
 * Synchronise les comptes sociaux d'un influenceur à partir d'un formulaire
 * (URL + nombre d'abonnés par plateforme).
 */
final class SocialAccountService
{
    /**
     * @param  array<string, array{url?: ?string, followers?: mixed}>  $social  indexé par clé de plateforme
     * @param  bool  $alwaysSnapshot  true : un relevé est créé même si le nombre d'abonnés est inchangé
     */
    public function syncForUser(User $user, array $social, ?int $recordedBy = null, bool $alwaysSnapshot = false): void
    {
        $knownPlatforms = array_keys(config('influboss.platforms', []));

        DB::transaction(function () use ($user, $social, $recordedBy, $alwaysSnapshot, $knownPlatforms) {
            foreach ($social as $platform => $input) {
                if (! in_array($platform, $knownPlatforms, true)) {
                    continue;
                }

                $url = trim((string) ($input['url'] ?? ''));
                $followers = $this->normalizeFollowers($input['followers'] ?? null);

                if ($url === '') {
                    // URL vidée : le compte et son historique disparaissent (cascade).
                    $user->socialAccounts()->where('platform', $platform)->delete();

                    continue;
                }

                $account = $this->upsert($user, $platform, $url);

                if ($followers === null) {
                    continue;
                }

                $unchanged = $account->followers_count === $followers && $account->snapshots()->exists();
                if ($alwaysSnapshot || ! $unchanged) {
                    $account->recordFollowers($followers, FollowerSnapshot::SOURCE_MANUAL, $recordedBy);
                }
            }
        });

        $user->unsetRelation('socialAccounts');
    }

    /**
     * Crée ou met à jour le compte d'une plateforme à partir d'une URL ou d'un @handle.
     */
    public function upsert(User $user, string $platform, string $urlOrHandle): SocialAccount
    {
        return SocialAccount::updateOrCreate(
            ['user_id' => $user->id, 'platform' => $platform],
            SocialAccount::parseInput($platform, $urlOrHandle)
        );
    }

    private function normalizeFollowers(mixed $value): ?int
    {
        if ($value === null || $value === '' || ! is_numeric($value)) {
            return null;
        }

        return max(0, (int) $value);
    }
}
