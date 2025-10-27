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
        Schema::create('transactions', function (Blueprint $table) {
            $table->uuid('id_transaction')->primary();
            $table->foreignUuid('id_compte')->constrained('comptes', 'id_compte')->onDelete('cascade');
            $table->enum('type_transaction', ['depot', 'retrait', 'salaire']);
            $table->decimal('montant', 15, 2);
            $table->timestamp('date_transaction')->useCurrent();
            $table->enum('statut', ['success', 'echec'])->default('success');
            $table->text('description')->nullable();
            $table->timestamps();

            // Index supplémentaires
            $table->index('type_transaction');
            $table->index('date_transaction');
            $table->index('statut');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};
