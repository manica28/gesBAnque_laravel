<?php

namespace App\Events;

use App\Models\Client;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ClientNotification
{
    use Dispatchable, SerializesModels;

    public $client;
    public $password;
    public $verificationCode;

    /**
     * Créer une nouvelle instance d'événement.
     */
    public function __construct(Client $client, string $password, string $verificationCode)
    {
        $this->client = $client;
        $this->password = $password;
        $this->verificationCode = $verificationCode;
    }
}