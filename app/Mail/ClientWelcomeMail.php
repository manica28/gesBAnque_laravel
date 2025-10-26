<?php

namespace App\Mail;

use App\Models\Client;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ClientWelcomeMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public $client;
    public $password;

    /**
     * Créer une nouvelle instance du message.
     */
    public function __construct(Client $client, string $password)
    {
        $this->client = $client;
        $this->password = $password;
    }

    /**
     * Obtenir l'enveloppe du message.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Bienvenue à la Banque - Vos identifiants de connexion',
        );
    }

    /**
     * Obtenir la définition du contenu du message.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.client-welcome',
            with: [
                'client' => $this->client,
                'password' => $this->password,
            ],
        );
    }

    /**
     * Obtenir les pièces jointes pour le message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        return [];
    }
}