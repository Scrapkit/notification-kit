<?php

declare(strict_types=1);

namespace Scrapkit\NotificationKit\Mail;

use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Scrapkit\NotificationKit\Domain\Outbox\Models\OutboxMessage;

/**
 * Sends the snapshot an approver signed off on, never a re-render.
 */
final class OutboxMail extends Mailable
{
    public function __construct(private readonly OutboxMessage $message) {}

    public function envelope(): Envelope
    {
        return new Envelope(subject: $this->message->rendered_subject);
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'notification-kit::mail.managed',
            with: ['renderedBody' => $this->message->rendered_body_html],
        );
    }
}
