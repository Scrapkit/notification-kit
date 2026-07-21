<?php

declare(strict_types=1);

namespace Scrapkit\NotificationKit\Http\Controllers;

use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Scrapkit\NotificationKit\Domain\Outbox\Enums\OutboxStatus;
use Scrapkit\NotificationKit\Domain\Outbox\Models\OutboxMessage;
use Scrapkit\NotificationKit\Http\Requests\ListOutboxMessagesRequest;
use Scrapkit\NotificationKit\Http\Resources\OutboxMessageResource;

final class ListOutboxMessagesController
{
    public function __invoke(ListOutboxMessagesRequest $request): AnonymousResourceCollection
    {
        $query = OutboxMessage::query()->with(['template', 'requestedBy', 'decidedBy'])->latestFirst();

        if ($request->filled('status')) {
            $query->withStatus(OutboxStatus::from($request->string('status')->toString()));
        }

        if ($request->filled('template_key')) {
            $query->forTemplateKey($request->string('template_key')->toString());
        }

        if ($request->filled('search')) {
            $query->search($request->string('search')->toString());
        }

        return OutboxMessageResource::collection($query->paginate($request->integer('per_page', 25)));
    }
}
