<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Paiements aux influenceurs (prévus ou effectués), rattachés si besoin à un contrat ou à une mission.
     */
    public function up(): void
    {
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('influencer_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('contract_id')->nullable()->constrained('contracts')->nullOnDelete();
            $table->foreignId('assignment_id')->nullable()->constrained('assignments')->nullOnDelete();
            $table->string('label');
            $table->decimal('amount', 15, 2); // FCFA
            $table->date('due_date')->nullable();
            $table->string('status', 20)->default('pending'); // clé de config('influboss.payment_statuses')
            $table->date('paid_at')->nullable();
            $table->string('method', 20)->nullable(); // clé de config('influboss.payment_methods')
            $table->string('reference', 100)->nullable();
            $table->string('receipt_path')->nullable(); // disque privé « local »
            $table->string('receipt_original_name')->nullable();
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['status', 'due_date']);
            $table->index(['influencer_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
