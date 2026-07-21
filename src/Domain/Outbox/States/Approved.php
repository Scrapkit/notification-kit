<?php

declare(strict_types=1);

namespace Scrapkit\NotificationKit\Domain\Outbox\States;

use Scrapkit\NotificationKit\Domain\Outbox\Enums\OutboxStatus;

final class Approved extends OutboxState
{
    public static function status(): OutboxStatus
    {
        return OutboxStatus::Approved;
    }

    public function allowedTransitions(): array
    {
        return [OutboxStatus::Sent, OutboxStatus::Failed];
    }
}
