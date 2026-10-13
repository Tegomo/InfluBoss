<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Un compte social par (influenceur, plateforme) avec le dernier nombre d'abonnés connu.
     */
    public function up(): void
    {
        Schema::create('social_accounts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('platform', 20); // clé de config('influboss.platforms'), validée côté applicatif
            $table->string('url')->nullable();
            $table->string('handle')->nullable();
            $table->unsignedBigInteger('followers_count')->nullable(); // null = inconnu
            $table->timestamp('last_synced_at')->nullable();
            $table->timestamps();

            $table->unique(['user_id', 'platform']);
            $table->index('platform');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('social_accounts');
    }
};
