<?php

declare(strict_types=1);

namespace Scrapkit\NotificationKit\Domain\Outbox\Actions;

use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Event;
use Scrapkit\NotificationKit\Domain\Outbox\Enums\OutboxStatus;
use Scrapkit\NotificationKit\Domain\Outbox\Events\MessageCancelled;
use Scrapkit\NotificationKit\Domain\Outbox\Models\OutboxMessage;

final class CancelOutboxMessageAction
{
    public function execute(OutboxMessage $message, ?Authenticatable $decidedBy): OutboxMessage
    {
        $message->decided_by_type = $decidedBy instanceof Model ? $decidedBy->getMorphClass() : null;
        $message->decided_by_id = $decidedBy instanceof Model ? $decidedBy->getKey() : null;
        $message->decided_at = now();
        $message->transitionTo(OutboxStatus::Cancelled);

        Event::dispatch(new MessageCancelled($message));

        return $message;
    }
}
