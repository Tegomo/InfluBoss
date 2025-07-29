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
        Schema::create('supervisor_influencer', function (Blueprint $table) {
            $table->id();
            $table->foreignId('supervisor_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('influencer_id')->constrained('users')->onDelete('cascade');
            $table->timestamps();
            
            // Empêcher qu'un influenceur ait plusieurs superviseurs
            $table->unique(['influencer_id']);
            
            // Empêcher les doublons d'assignation
            $table->unique(['supervisor_id', 'influencer_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('supervisor_influencer');
    }
};
