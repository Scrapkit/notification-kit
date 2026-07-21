<?php

declare(strict_types=1);

namespace Scrapkit\NotificationKit\Domain\Outbox\DataTransferObjects;

use Scrapkit\NotificationKit\Domain\Outbox\Enums\SendOutcome;
use Scrapkit\NotificationKit\Domain\Outbox\Models\OutboxMessage;

/**
 * What happened when the host asked the kit to send a managed mailable.
 */
final readonly class SendDispatch
{
    public function __construct(
        public SendOutcome $outcome,
        public ?OutboxMessage $message = null,
    ) {}

    public function needsConfirmation(): bool
    {
        return $this->outcome === SendOutcome::PendingConfirmation;
    }
}
