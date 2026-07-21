<?php

declare(strict_types=1);

namespace Scrapkit\NotificationKit\Domain\Outbox\States;

use Scrapkit\NotificationKit\Domain\Outbox\Enums\OutboxStatus;

abstract class OutboxState
{
    abstract public static function status(): OutboxStatus;

    /**
     * @return list<OutboxStatus>
     */
    abstract public function allowedTransitions(): array;

    final public function canTransitionTo(OutboxStatus $target): bool
    {
        return in_array($target, $this->allowedTransitions(), true);
    }

    final public static function for(OutboxStatus $status): self
    {
        return match ($status) {
            OutboxStatus::Pending => new Pending,
            OutboxStatus::Approved => new Approved,
            OutboxStatus::Cancelled => new Cancelled,
            OutboxStatus::Sent => new Sent,
            OutboxStatus::Failed => new Failed,
        };
    }
}
