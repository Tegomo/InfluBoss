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
        Schema::create('contents', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('description')->nullable();
            $table->foreignId('campaign_id')->constrained()->onDelete('cascade');
            $table->foreignId('created_by')->constrained('users');
            $table->enum('type', ['image', 'video', 'text', 'link', 'other'])->default('image');
            $table->string('file_path')->nullable(); // Chemin vers le fichier (image/vidéo)
            $table->text('content_text')->nullable(); // Texte du contenu
            $table->json('guidelines')->nullable(); // Directives d'utilisation du contenu
            $table->enum('status', ['draft', 'approved', 'archived'])->default('draft');
            $table->date('publication_date')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('contents');
    }
};
