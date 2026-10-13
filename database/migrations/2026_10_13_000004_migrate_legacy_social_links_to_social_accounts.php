<?php

use App\Services\LegacySocialLinksMigrator;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Copie les liens sociaux existants vers social_accounts (idempotent, sans suppression).
     */
    public function up(): void
    {
        app(LegacySocialLinksMigrator::class)->migrate();
    }

    /**
     * Aucune donnée n'est détruite au rollback : les anciennes colonnes sont intactes.
     */
    public function down(): void
    {
        //
    }
};
