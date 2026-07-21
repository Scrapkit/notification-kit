<?php

declare(strict_types=1);

namespace Scrapkit\NotificationKit\Mail;

use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Scrapkit\NotificationKit\Contracts\Manageable;
use Scrapkit\NotificationKit\Domain\Rendering\RenderedContent;
use Scrapkit\NotificationKit\Domain\Rendering\TemplateRenderer;
use Scrapkit\NotificationKit\Domain\Templates\DataTransferObjects\EffectiveTemplate;
use Scrapkit\NotificationKit\Domain\Templates\TemplateResolver;

/**
 * Base class for host mailables whose subject and body are editable.
 */
abstract class ManagedMailable extends Mailable implements Manageable
{
    /**
     * The values the template placeholders resolve against.
     *
     * @return array<string, mixed>
     */
    abstract public function templateData(): array;

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
