<?php

declare(strict_types=1);

namespace Scrapkit\NotificationKit\Http\Controllers;

use Scrapkit\NotificationKit\Domain\Outbox\Models\OutboxMessage;
use Scrapkit\NotificationKit\Http\Requests\ViewOutboxMessageRequest;
use Scrapkit\NotificationKit\Http\Resources\OutboxMessageResource;

final class ShowOutboxMessageController
{
    public function __invoke(ViewOutboxMessageRequest $request, OutboxMessage $message): OutboxMessageResource
    {
        return OutboxMessageResource::make($message->load(['template', 'requestedBy', 'decidedBy']));
    }
}
