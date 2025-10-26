<?php

namespace App\Listeners;

use App\Events\ClientNotification;
use App\Mail\ClientWelcomeMail;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Mail;
use Twilio\Rest\Client as TwilioClient;

class SendClientNotification implements ShouldQueue
{
    use InteractsWithQueue;

    protected $twilio;

    /**
     * Créer une nouvelle instance du listener.
     */
    public function __construct()
    {
        $this->twilio = new TwilioClient(
            config('services.twilio.sid'),
            config('services.twilio.token')
        );
    }

    /**
     * Gérer l'événement.
     */
    public function handle(ClientNotification $event): void
    {
        try {
            // Envoyer l'email d'authentification
            Mail::to($event->client->user->email)->send(new ClientWelcomeMail($event->client, $event->password));
        } catch (\Exception $e) {
            // Log l'erreur mais ne pas interrompre le processus
            \Log::error('Erreur lors de l\'envoi de l\'email: ' . $e->getMessage());
        }

        // Envoyer le SMS avec le code de vérification
        $this->sendSms($event->client->user->telephone, $event->verificationCode);
    }

    /**
     * Envoyer un SMS via Twilio.
     */
    protected function sendSms(string $to, string $code): void
    {
        try {
            $this->twilio->messages->create(
                $to,
                [
                    'from' => config('services.twilio.from'),
                    'body' => "Votre code de vérification est : {$code}. Utilisez-le uniquement lors de votre première connexion."
                ]
            );
        } catch (\Exception $e) {
            // Log l'erreur mais ne pas interrompre le processus
            \Log::error('Erreur lors de l\'envoi du SMS: ' . $e->getMessage());
        }
    }
}