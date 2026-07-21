<?php

declare(strict_types=1);

namespace Scrapkit\NotificationKit\Domain\Outbox\States;

use Scrapkit\NotificationKit\Domain\Outbox\Enums\OutboxStatus;

final class Pending extends OutboxState
{
    public static function status(): OutboxStatus
    {
        return OutboxStatus::Pending;
    }

    public function allowedTransitions(): array
    {
        return [OutboxStatus::Approved, OutboxStatus::Cancelled];
    }
}
