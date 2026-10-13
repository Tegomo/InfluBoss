<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Seuils d'alerte d'expiration déjà traités, pour ne jamais envoyer deux fois la même alerte.
     */
    public function up(): void
    {
        Schema::create('contract_alerts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('contract_id')->constrained('contracts')->cascadeOnDelete();
            $table->unsignedSmallInteger('days_before');
            $table->timestamp('sent_at');
            $table->timestamps();

            $table->unique(['contract_id', 'days_before']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('contract_alerts');
    }
};
