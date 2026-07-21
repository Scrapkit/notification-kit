<?php

declare(strict_types=1);

namespace Scrapkit\NotificationKit\Http\Controllers;

use Scrapkit\NotificationKit\Domain\Outbox\Actions\CancelOutboxMessageAction;
use Scrapkit\NotificationKit\Domain\Outbox\Models\OutboxMessage;
use Scrapkit\NotificationKit\Http\Requests\DecideOutboxMessageRequest;
use Scrapkit\NotificationKit\Http\Resources\OutboxMessageResource;

final class CancelOutboxMessageController
{
    public function __invoke(
        DecideOutboxMessageRequest $request,
        OutboxMessage $message,
        CancelOutboxMessageAction $action,
    ): OutboxMessageResource {
        return OutboxMessageResource::make($action->execute($message, $request->user()));
    }
}
