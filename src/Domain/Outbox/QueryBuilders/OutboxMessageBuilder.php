<?php

declare(strict_types=1);

namespace Scrapkit\NotificationKit\Domain\Outbox\QueryBuilders;

use Illuminate\Database\Eloquent\Builder;
use Scrapkit\NotificationKit\Domain\Outbox\Enums\OutboxStatus;
use Scrapkit\NotificationKit\Domain\Outbox\Models\OutboxMessage;

/**
 * @extends Builder<OutboxMessage>
 */
final class OutboxMessageBuilder extends Builder
{
    public function withStatus(OutboxStatus $status): self
    {
        return $this->where('status', $status->value);
    }

    public function forTemplateKey(string $key): self
    {
        return $this->where('template_key', $key);
    }

    public function search(string $term): self
    {
        return $this->where(function (self $query) use ($term): void {
            $query->where('rendered_subject', 'like', "%{$term}%")
                ->orWhere('recipients', 'like', "%{$term}%");
        });
    }

    public function latestFirst(): self
    {
        return $this->orderByDesc('created_at')->orderByDesc('id');
    }
}
