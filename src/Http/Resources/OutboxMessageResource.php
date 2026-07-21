<?php

declare(strict_types=1);

namespace Scrapkit\NotificationKit\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Scrapkit\NotificationKit\Domain\Outbox\Models\OutboxMessage;
use Scrapkit\NotificationKit\Support\UserDisplay;

/**
 * @mixin OutboxMessage
 */
final class OutboxMessageResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'uuid' => $this->uuid,
            'template_key' => $this->template_key,
            'template_name' => $this->template?->name,
            'mailable_class' => $this->mailable_class,
            'rendered_subject' => $this->rendered_subject,
            'rendered_body_html' => $this->rendered_body_html,
            'recipients' => $this->recipients,
            'status' => $this->status->value,
            'requested_by' => UserDisplay::name($this->requestedBy),
            'decided_by' => UserDisplay::name($this->decidedBy),
            'decided_at' => $this->decided_at?->toIso8601String(),
            'sent_at' => $this->sent_at?->toIso8601String(),
            'error' => $this->error,
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}
