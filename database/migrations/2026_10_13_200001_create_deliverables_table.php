<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Livrables attendus d'une mission (cahier des charges) : type, quantité, échéance, statut.
     */
    public function up(): void
    {
        Schema::create('deliverables', function (Blueprint $table) {
            $table->id();
            $table->foreignId('assignment_id')->constrained('assignments')->cascadeOnDelete();
            $table->string('type', 20); // clé de config('influboss.deliverable_types')
            $table->string('platform', 20)->nullable(); // clé de config('influboss.platforms')
            $table->unsignedSmallInteger('quantity')->default(1);
            $table->date('due_date')->nullable();
            $table->text('description')->nullable();
            $table->string('status', 20)->default('todo'); // clé de config('influboss.deliverable_statuses')
            $table->boolean('status_set_manually')->default(false); // statut forcé tant qu'aucune publication n'est rattachée
            $table->timestamps();

            $table->index(['assignment_id', 'due_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('deliverables');
    }
};
