<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('stats', function (Blueprint $table) {
            $table->id();
            $table->foreignId('publication_id')->constrained()->onDelete('cascade');
            $table->dateTime('collected_at');
            $table->integer('likes')->default(0);
            $table->integer('comments')->default(0);
            $table->integer('shares')->default(0);
            $table->integer('saves')->default(0);
            $table->integer('views')->nullable(); // Pour les vidéos/stories
            $table->integer('clicks')->nullable(); // Pour les liens
            $table->decimal('engagement_rate', 8, 4)->nullable(); // Taux d'engagement
            $table->decimal('reach', 15, 2)->nullable(); // Portée estimée
            $table->decimal('impressions', 15, 2)->nullable(); // Impressions
            $table->json('demographics')->nullable(); // Démographie des interactions
            $table->json('raw_data')->nullable(); // Données brutes de l'API
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stats');
    }
};
