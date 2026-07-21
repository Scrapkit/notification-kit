<?php

declare(strict_types=1);

namespace Scrapkit\NotificationKit\Http\Controllers;

use Scrapkit\NotificationKit\Domain\Outbox\Actions\ApproveOutboxMessageAction;
use Scrapkit\NotificationKit\Domain\Outbox\Models\OutboxMessage;
use Scrapkit\NotificationKit\Http\Requests\DecideOutboxMessageRequest;
use Scrapkit\NotificationKit\Http\Resources\OutboxMessageResource;

final class ApproveOutboxMessageController
{
    public function __invoke(
        DecideOutboxMessageRequest $request,
        OutboxMessage $message,
        ApproveOutboxMessageAction $action,
    ): OutboxMessageResource {
        return OutboxMessageResource::make($action->execute($message, $request->user()));
    }
}
