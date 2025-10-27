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
        Schema::create('admins', function (Blueprint $table) {
            $table->uuid('id_admin')->primary();
            $table->foreignUuid('id_user')
                  ->references('id_user') // colonne primaire réelle de users
                  ->on('users')
                  ->onDelete('cascade');
            $table->json('permissions')->nullable(); // Permissions sous forme de JSON
            $table->timestamps();

            $table->unique('id_user'); // Contrainte d'unicité sur id_user
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('admins');
    }
};
