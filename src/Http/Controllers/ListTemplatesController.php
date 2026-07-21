<?php

declare(strict_types=1);

namespace Scrapkit\NotificationKit\Http\Controllers;

use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Scrapkit\NotificationKit\Domain\Templates\Enums\TemplateType;
use Scrapkit\NotificationKit\Domain\Templates\Models\NotificationTemplate;
use Scrapkit\NotificationKit\Http\Requests\ListTemplatesRequest;
use Scrapkit\NotificationKit\Http\Resources\TemplateResource;

final class ListTemplatesController
{
    public function __invoke(ListTemplatesRequest $request): AnonymousResourceCollection
    {
        $query = NotificationTemplate::query()->ordered();

        $archived = $request->string('archived')->toString();

        match ($archived) {
            'only' => $query->onlyArchived(),
            'with' => null,
            default => $query->withoutArchived(),
        };

        if ($request->filled('type')) {
            $query->ofType(TemplateType::from($request->string('type')->toString()));
        }

        if ($request->has('requires_confirmation')) {
            $query->requiresConfirmation($request->boolean('requires_confirmation'));
        }

        if ($request->filled('search')) {
            $query->search($request->string('search')->toString());
        }

        return TemplateResource::collection($query->paginate($request->integer('per_page', 25)));
    }
}
