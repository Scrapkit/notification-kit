<?php

declare(strict_types=1);

namespace Scrapkit\NotificationKit\Domain\Outbox\States;

use Scrapkit\NotificationKit\Domain\Outbox\Enums\OutboxStatus;

final class Sent extends OutboxState
{
    public static function status(): OutboxStatus
    {
        return OutboxStatus::Sent;
    }

    public function allowedTransitions(): array
    {
        return [];
    }
}
