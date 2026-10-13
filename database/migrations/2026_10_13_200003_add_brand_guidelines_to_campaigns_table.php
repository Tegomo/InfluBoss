<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Charte de marque de la campagne : ton, consignes visuelles, hashtags et mentions obligatoires,
     * produits ou services à promouvoir.
     */
    public function up(): void
    {
        Schema::table('campaigns', function (Blueprint $table) {
            foreach ([
                'brand_tone' => fn () => $table->text('brand_tone')->nullable(),
                'visual_guidelines' => fn () => $table->text('visual_guidelines')->nullable(),
                'required_hashtags' => fn () => $table->json('required_hashtags')->nullable(),
                'required_mentions' => fn () => $table->json('required_mentions')->nullable(),
                'products' => fn () => $table->text('products')->nullable(),
            ] as $column => $add) {
                if (! Schema::hasColumn('campaigns', $column)) {
                    $add();
                }
            }
        });
    }

    public function down(): void
    {
        Schema::table('campaigns', function (Blueprint $table) {
            foreach (['brand_tone', 'visual_guidelines', 'required_hashtags', 'required_mentions', 'products'] as $column) {
                if (Schema::hasColumn('campaigns', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
