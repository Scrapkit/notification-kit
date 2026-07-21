<?php

declare(strict_types=1);

namespace Scrapkit\NotificationKit\Domain\Outbox\Exceptions;

use Scrapkit\NotificationKit\Domain\Outbox\Enums\OutboxStatus;
use Scrapkit\NotificationKit\Exceptions\NotificationKitException;

final class InvalidOutboxTransition extends NotificationKitException
{
    public static function make(OutboxStatus $from, OutboxStatus $to): self
    {
        return new self("Cannot transition an outbox message from [{$from->value}] to [{$to->value}].");
    }
}
