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
        Schema::table('assignments', function (Blueprint $table) {
            $table->date('deadline')->nullable()->after('status');
            $table->text('description')->nullable()->after('compensation');
            $table->text('instructions')->nullable()->after('description');
            $table->integer('required_posts')->default(1)->after('instructions');
            $table->json('platforms')->nullable()->after('required_posts');
            // Renommer la colonne required_deliverables en required_posts si elle existe déjà
            if (Schema::hasColumn('assignments', 'required_deliverables')) {
                $table->dropColumn('required_deliverables');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('assignments', function (Blueprint $table) {
            $table->dropColumn(['deadline', 'description', 'instructions', 'required_posts', 'platforms']);
            $table->json('required_deliverables')->nullable()->after('compensation');
        });
    }
};
