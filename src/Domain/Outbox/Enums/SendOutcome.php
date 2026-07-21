<?php

declare(strict_types=1);

namespace Scrapkit\NotificationKit\Domain\Outbox\Enums;

enum SendOutcome: string
{
    case SentDirectly = 'sent_directly';
    case PendingConfirmation = 'pending_confirmation';
}
