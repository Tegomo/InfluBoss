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
            // Ajouter pseudo (nom d'utilisateur sur les réseaux sociaux)
            if (!Schema::hasColumn('users', 'pseudo')) {
                $table->string('pseudo')->nullable()->after('name');
            }
            
            // Ajouter date de naissance
            if (!Schema::hasColumn('users', 'birthdate')) {
                $table->date('birthdate')->nullable()->after('pseudo');
            }
            
            // Vérifier si phone existe déjà, sinon l'ajouter
            if (!Schema::hasColumn('users', 'phone')) {
                $table->string('phone')->nullable()->after('email');
            }
            
            // Ajouter ville
            if (!Schema::hasColumn('users', 'city')) {
                $table->string('city')->nullable()->after('phone');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Ne rien supprimer si on fait un rollback, pour éviter les erreurs
            // lors de la suppression de colonnes qui n'existeraient pas
        });
    }
};
