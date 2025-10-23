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
        Schema::create('comptes', function (Blueprint $table) {
            $table->uuid('id_compte')->primary();
            $table->string('numero_compte')->unique();
            $table->foreignUuid('id_client')->constrained('clients', 'id_client')->onDelete('cascade');
            $table->string('titulaire');
            $table->enum('type_compte', ['Epargne', 'Courant', 'Cheque']);
            $table->decimal('solde', 15, 2);
            $table->timestamp('date_creation')->useCurrent();
            $table->enum('statut', ['actif', 'inactif', 'bloque', 'suspendu'])->default('actif');
            $table->timestamps();

            // Index supplémentaires
            $table->index('type_compte');
            $table->index('statut');
            $table->index('date_creation');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('comptes');
    }
};
