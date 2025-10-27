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
        Schema::create('clients', function (Blueprint $table) {
            $table->uuid('id_client')->primary();

            // Clé étrangère vers users.id_user
            $table->uuid('id_user');
            $table->foreign('id_user')
                  ->references('id_user')
                  ->on('users')
                  ->onDelete('cascade');

            $table->string('nci')->nullable()->unique(); // Numéro de carte d'identité
            $table->string('email')->unique(); // Email du client
            $table->string('telephone')->unique(); // Téléphone du client
            $table->string('adresse'); // Adresse du client
            $table->string('titulaire'); // Nom du titulaire
            $table->string('password'); // Mot de passe généré
            $table->string('code')->nullable(); // Code pour première connexion
            $table->decimal('solde_initial', 15, 2)->default(0);
            $table->timestamps();

            $table->unique('id_user'); // Contrainte d'unicité
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('clients');
    }
};
