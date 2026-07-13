<?php

namespace App\Mail;

use App\Models\Invitation;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class InvitationMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(
        public Invitation $invitation,
        public string $tenantName,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "{$this->tenantName} sizi Vadexa ekibine davet etti",
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.invitation',
            with: [
                'tenantName' => $this->tenantName,
                'acceptUrl' => route('invitations.accept', $this->invitation->token),
                'role' => $this->invitation->role === 'owner' ? 'Yönetici' : 'Üye',
            ],
        );
    }
}
