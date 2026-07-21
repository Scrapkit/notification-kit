<?php

declare(strict_types=1);

namespace Scrapkit\NotificationKit\Domain\Outbox\Actions;

use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Event;
use Scrapkit\NotificationKit\Domain\Outbox\DataTransferObjects\RecipientsSnapshot;
use Scrapkit\NotificationKit\Domain\Outbox\Enums\OutboxStatus;
use Scrapkit\NotificationKit\Domain\Outbox\Events\MessagePendingConfirmation;
use Scrapkit\NotificationKit\Domain\Outbox\Models\OutboxMessage;
use Scrapkit\NotificationKit\Domain\Rendering\TemplateRenderer;
use Scrapkit\NotificationKit\Domain\Templates\DataTransferObjects\EffectiveTemplate;
use Scrapkit\NotificationKit\Domain\Templates\Models\NotificationTemplate;
use Scrapkit\NotificationKit\Mail\ManagedMailable;

/**
 * Holds a confirmable email as a pending outbox message.
 *
 * The content is rendered and stored now so an approver decides on exactly
 * what they are shown, whatever happens to the template or the underlying
 * models afterwards.
 */
final class RequestConfirmableSendAction
{
    public function __construct(private readonly TemplateRenderer $renderer) {}

    public function execute(
        ManagedMailable $mailable,
        EffectiveTemplate $effective,
        RecipientsSnapshot $recipients,
        ?Authenticatable $requestedBy,
    ): OutboxMessage {
        $content = $this->renderer->render($effective->subject, $effective->body, $mailable->templateData());

        $message = OutboxMessage::query()->create([
            'template_id' => NotificationTemplate::query()->where('key', $effective->key)->value('id'),
            'template_key' => $effective->key,
            'mailable_class' => $mailable::class,
            'rendered_subject' => $content->subject ?? $mailable::template()->name,
            'rendered_body_html' => $content->bodyHtml,
            'recipients' => $recipients->toArray(),
            'envelope' => null,
            'status' => OutboxStatus::Pending,
            'requested_by_type' => $requestedBy instanceof Model ? $requestedBy->getMorphClass() : null,
            'requested_by_id' => $requestedBy instanceof Model ? $requestedBy->getKey() : null,
        ]);

        Event::dispatch(new MessagePendingConfirmation($message));

        return $message;
    }
}
