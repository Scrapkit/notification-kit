<?php

declare(strict_types=1);

namespace Scrapkit\NotificationKit\Domain\Outbox\Events;

use Illuminate\Queue\SerializesModels;
use Scrapkit\NotificationKit\Domain\Outbox\Models\OutboxMessage;

final class MessageSent
{
    use SerializesModels;

    public function __construct(public readonly OutboxMessage $message) {}
}
