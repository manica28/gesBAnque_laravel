<?php

namespace App\Jobs;

use App\Models\Compte;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class UnblockExpiredAccounts implements ShouldQueue
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
        // Récupérer tous les comptes bloqués dont la date de fin de blocage est échue
        $expiredUnblockAccounts = Compte::where('statutBlocage', 'bloque')
            ->where('dateDeblocagePrevue', '<', now())
            ->get();

        foreach ($expiredUnblockAccounts as $compte) {
            // Débloquer le compte
            $compte->update([
                'statutBlocage' => 'actif',
                'motifBlocage' => null,
                'dateBlocage' => null,
                'dateDeblocagePrevue' => null,
            ]);

            Log::info("Compte {$compte->numero_compte} débloqué suite à expiration de la période de blocage.");
        }

        Log::info("Job UnblockExpiredAccounts exécuté : {$expiredUnblockAccounts->count()} comptes débloqués.");
    }
}
