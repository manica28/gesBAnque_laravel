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
        Schema::table('comptes', function (Blueprint $table) {
            $table->softDeletes(); // Ajoute deleted_at
            $table->string('devise')->default('FCFA')->after('solde');
            $table->text('motifBlocage')->nullable()->after('statut');
            $table->json('metadata')->nullable()->after('motifBlocage');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('comptes', function (Blueprint $table) {
            // Supprimer softDeletes
            if (Schema::hasColumn('comptes', 'deleted_at')) {
                $table->dropSoftDeletes();
            }
            
            // Supprimer les autres colonnes une par une pour éviter les erreurs
            $columns = ['devise', 'motifBlocage', 'metadata'];
            foreach ($columns as $column) {
                if (Schema::hasColumn('comptes', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
