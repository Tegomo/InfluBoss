<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Historique du nombre d'abonnés d'un compte social (saisie manuelle ou synchronisation API).
     */
    public function up(): void
    {
        Schema::create('follower_snapshots', function (Blueprint $table) {
            $table->id();
            $table->foreignId('social_account_id')->constrained('social_accounts')->cascadeOnDelete();
            $table->unsignedBigInteger('followers_count');
            $table->timestamp('collected_at');
            $table->string('source', 10)->default('manual'); // manual|api, validé côté applicatif
            $table->foreignId('recorded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['social_account_id', 'collected_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('follower_snapshots');
    }
};
