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
        Schema::create('publications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('assignment_id')->constrained()->onDelete('cascade');
            $table->foreignId('content_id')->nullable()->constrained()->onDelete('set null');
            $table->foreignId('influencer_id')->constrained('users')->onDelete('cascade');
            $table->string('platform'); // Facebook, Instagram, TikTok, etc.
            $table->string('post_url'); // URL de la publication
            $table->string('post_id')->nullable(); // ID de la publication sur la plateforme
            $table->text('post_content')->nullable(); // Contenu textuel du post
            $table->enum('post_type', ['post', 'story', 'reel', 'video', 'other'])->default('post');
            $table->datetime('published_at');
            $table->enum('status', ['pending_verification', 'verified', 'rejected'])->default('pending_verification');
            $table->foreignId('verified_by')->nullable()->constrained('users')->onDelete('set null');
            $table->datetime('verified_at')->nullable();
            $table->text('rejection_reason')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('publications');
    }
};
