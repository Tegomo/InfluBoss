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
        Schema::table('users', function (Blueprint $table) {
            $table->string('instagram_handle')->nullable()->after('email');
            $table->string('twitter_handle')->nullable()->after('instagram_handle');
            $table->string('facebook_handle')->nullable()->after('twitter_handle');
            $table->string('youtube_channel')->nullable()->after('facebook_handle');
            $table->string('tiktok_handle')->nullable()->after('youtube_channel');
            $table->string('linkedin_profile')->nullable()->after('tiktok_handle');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('linkedin_profile');
            $table->dropColumn('tiktok_handle');
            $table->dropColumn('youtube_channel');
            $table->dropColumn('facebook_handle');
            $table->dropColumn('twitter_handle');
            $table->dropColumn('instagram_handle');
        });
    }
};
