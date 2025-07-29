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
        Schema::create('api_configurations', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('api_type'); // brightdata_social, brightdata_serp, brightdata_web_unlocker
            $table->string('api_key');
            $table->string('username')->nullable();
            $table->string('password')->nullable();
            $table->string('endpoint_url')->nullable();
            $table->json('additional_config')->nullable(); // Autres configurations spécifiques
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('api_configurations');
    }
};
