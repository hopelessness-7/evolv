<?php

namespace App\Modules\Notifications\Mail;

use App\Modules\Notifications\Models\UserNotification;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class UserNotificationMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public readonly UserNotification $notification,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: $this->notification->title,
        );
    }

    public function content(): Content
    {
        return new Content(
            text: 'notifications::mail',
            with: [
                'title' => $this->notification->title,
                'body' => $this->notification->body,
                'type' => $this->notification->type->value,
            ],
        );
    }
}
