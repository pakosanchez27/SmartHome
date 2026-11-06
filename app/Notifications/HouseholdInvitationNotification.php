<?php

namespace App\Notifications;

use App\Models\HouseholdInvitation;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

/**
 * EP03-04: correo de invitación al hogar.
 *
 * El enlace lleva el token crudo; la base solo conoce su hash (uso único, caduca en 7 días).
 */
class HouseholdInvitationNotification extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public HouseholdInvitation $invitation,
        public string $rawToken,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: $this->invitation->invitedBy->name.' te invitó a su hogar en SmartHome',
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.household-invitation',
            with: [
                'invitation' => $this->invitation,
                'url' => route('invitation.show', $this->rawToken),
            ],
        );
    }
}
