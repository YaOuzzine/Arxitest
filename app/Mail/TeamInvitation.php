<?php

// app/Mail/TeamInvitation.php

namespace App\Mail;

use App\Models\Team;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class TeamInvitation extends Mailable
{
    use Queueable, SerializesModels;

    public $team;
    public $inviterName;
    public $token;
    public $role;
    public $registrationLink;
    public $isRegistered;

    /**
     * Create a new message instance.
     */
    public function __construct(Team $team, string $inviterName, string $token, string $role, bool $isRegistered = false)
    {
        $this->team = $team;
        $this->inviterName = $inviterName;
        $this->token = $token;
        $this->role = $role;
        $this->isRegistered = $isRegistered;

        // Create registration link with invitation token
        $this->registrationLink = $isRegistered
            ? route('invitations.accept', $token)
            : route('register') . '?invitation=' . $token;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "Invitation to join {$this->team->name} on Arxitest",
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.team-invitation',
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        return [];
    }
}
