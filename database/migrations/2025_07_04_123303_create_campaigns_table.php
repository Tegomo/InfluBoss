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
        Schema::create('campaigns', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description');
            $table->foreignId('created_by')->constrained('users');
            $table->date('start_date');
            $table->date('end_date');
            $table->enum('status', ['draft', 'active', 'paused', 'completed'])->default('draft');
            $table->decimal('budget', 15, 2)->nullable();
            $table->string('target_audience')->nullable();
            $table->json('objectives')->nullable(); // Objectifs de la campagne (KPIs)
            $table->json('platforms')->nullable(); // Plateformes ciblées (Facebook, Instagram, etc.)
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('campaigns');
    }
};
