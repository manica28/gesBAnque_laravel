<?php

namespace App\Jobs;

use App\Models\Compte;
use App\Models\Transaction;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class ArchiveExpiredBlockedAccounts implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct()
    {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        // Récupérer tous les comptes bloqués dont la date de début de blocage est échue
        $expiredBlockedAccounts = Compte::where('statutBlocage', 'bloque')
            ->where('dateBlocage', '<', now())
            ->get();

        foreach ($expiredBlockedAccounts as $compte) {
            // Archiver le compte (soft delete)
            $compte->delete();

            // Archiver toutes les transactions associées
            Transaction::where('id_compte', $compte->id_compte)->delete();

            Log::info("Compte {$compte->numero_compte} et ses transactions archivés suite à expiration du blocage.");
        }

        Log::info("Job ArchiveExpiredBlockedAccounts exécuté : {$expiredBlockedAccounts->count()} comptes archivés.");
    }
}
