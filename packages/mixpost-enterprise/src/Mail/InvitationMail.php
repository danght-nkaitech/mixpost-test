<?php

namespace Inovector\MixpostEnterprise\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Inovector\Mixpost\Concerns\Mail;
use Inovector\MixpostEnterprise\Models\Invitation;

class InvitationMail extends Mailable implements ShouldQueue
{
    use Mail, Queueable, SerializesModels;

    public $deleteWhenMissingModels = true;

    public Invitation $invitation;

    public function __construct(Invitation $invitation)
    {
        $this->invitation = $invitation;
        $this->theme = $this->theme();
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: __('mixpost-enterprise::team.invited_join_workspace', ['name' => $this->invitation->workspace->name])
        );
    }

    public function content(): Content
    {
        $this->beforeSendMailMessage();

        return new Content(
            markdown: 'mixpost-enterprise::mail.invitation',
            with: [
                'workspace' => $this->invitation->workspace,
                'url' => route('mixpost_e.invitation', [
                    'invitation' => $this->invitation->uuid,
                ]),
            ]
        );
    }
}
