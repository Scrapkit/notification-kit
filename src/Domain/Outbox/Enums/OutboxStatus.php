<?php

declare(strict_types=1);

namespace Scrapkit\NotificationKit\Domain\Outbox\Enums;

enum OutboxStatus: string
{
    case Pending = 'pending';
    case Approved = 'approved';
    case Cancelled = 'cancelled';
    case Sent = 'sent';
    case Failed = 'failed';
}
