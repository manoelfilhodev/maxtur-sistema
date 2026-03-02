<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class FuncionarioActivationInviteMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public User $funcionario)
    {
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Convite de acesso - Systex Mobility',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.funcionario-activation',
            with: [
                'nome' => $this->funcionario->name,
                'activationLink' => route('activation.show', $this->funcionario->activation_token),
                'expiresAt' => optional($this->funcionario->activation_expires_at)->format('d/m/Y H:i'),
            ],
        );
    }

    public function attachments(): array
    {
        return [];
    }
}

