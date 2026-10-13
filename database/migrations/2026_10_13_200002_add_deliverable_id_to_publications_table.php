<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Livrable auquel une publication répond (facultatif).
     */
    public function up(): void
    {
        if (Schema::hasColumn('publications', 'deliverable_id')) {
            return;
        }

        Schema::table('publications', function (Blueprint $table) {
            $table->foreignId('deliverable_id')->nullable()->after('assignment_id')
                ->constrained('deliverables')->nullOnDelete();
        });
    }

    public function down(): void
    {
        if (! Schema::hasColumn('publications', 'deliverable_id')) {
            return;
        }

        Schema::table('publications', function (Blueprint $table) {
            $table->dropConstrainedForeignId('deliverable_id');
        });
    }
};
