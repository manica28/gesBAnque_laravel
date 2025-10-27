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
            if (!Schema::hasColumn('comptes', 'motifBlocage')) {
                $table->string('motifBlocage')->nullable();
            }
            if (!Schema::hasColumn('comptes', 'dateBlocage')) {
                $table->timestamp('dateBlocage')->nullable();
            }
            if (!Schema::hasColumn('comptes', 'dateDeblocagePrevue')) {
                $table->timestamp('dateDeblocagePrevue')->nullable();
            }
            if (!Schema::hasColumn('comptes', 'statutBlocage')) {
                $table->string('statutBlocage')->default('actif');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('comptes', function (Blueprint $table) {
            $table->dropColumn(['motifBlocage', 'dateBlocage', 'dateDeblocagePrevue', 'statutBlocage']);
        });
    }
};
