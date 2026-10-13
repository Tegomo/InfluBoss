<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Contrats des influenceurs (plusieurs par influenceur pour garder l'historique des renouvellements).
     */
    public function up(): void
    {
        Schema::create('contracts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('influencer_id')->constrained('users')->cascadeOnDelete();
            $table->string('type', 20); // clé de config('influboss.contract_types')
            $table->date('start_date');
            $table->date('end_date');
            $table->decimal('amount', 15, 2)->nullable(); // FCFA
            $table->text('payment_terms')->nullable();
            $table->string('status', 20)->default('negotiation'); // clé de config('influboss.contract_statuses')
            $table->string('file_path')->nullable(); // disque privé « local »
            $table->string('file_original_name')->nullable();
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['status', 'end_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('contracts');
    }
};
