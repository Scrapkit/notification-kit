<?php

declare(strict_types=1);

namespace Scrapkit\NotificationKit\Mail;

use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Mail\SentMessage;
use Scrapkit\NotificationKit\Contracts\Manageable;
use Scrapkit\NotificationKit\Domain\Rendering\RenderedContent;
use Scrapkit\NotificationKit\Domain\Rendering\TemplateRenderer;
use Scrapkit\NotificationKit\Domain\Templates\DataTransferObjects\EffectiveTemplate;
use Scrapkit\NotificationKit\Domain\Templates\TemplateResolver;
use Scrapkit\NotificationKit\Exceptions\ConfirmationRequiredException;

/**
 * Base class for host mailables whose subject and body are editable.
 */
abstract class ManagedMailable extends Mailable implements Manageable
{
    /**
     * Set by the kit's send pipeline once the confirmation rule has been
     * applied. Without it, sending a confirmable mailable straight through
     * the Mail facade would silently bypass the approval gate.
     */
    private bool $confirmationHandled = false;

    /**
     * The values the template placeholders resolve against.
     *
     * @return array<string, mixed>
     */
    abstract public function templateData(): array;

    public function confirmationHandled(): static
    {
        $this->confirmationHandled = true;

        return $this;
    }

    public function send($mailer): ?SentMessage
    {
        $effective = $this->effectiveTemplate();

        if ($effective->requiresConfirmation && ! $this->confirmationHandled) {
            throw ConfirmationRequiredException::for(static::class, $effective->key);
        }

        return parent::send($mailer);
    }

    public function envelope(): Envelope
    {
        return new Envelope(subject: $this->rendered()->subject ?? static::template()->name);
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'notification-kit::mail.managed',
            with: ['renderedBody' => $this->rendered()->bodyHtml],
        );
    }

    public function effectiveTemplate(): EffectiveTemplate
    {
        return app(TemplateResolver::class)->resolve(static::template());
    }

    public function rendered(): RenderedContent
    {
        $effective = $this->effectiveTemplate();

        return app(TemplateRenderer::class)->render(
            $effective->subject,
            $effective->body,
            $this->templateData(),
        );
    }
}
