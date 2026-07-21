<?php

declare(strict_types=1);

namespace Scrapkit\NotificationKit\Http\Controllers;

use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Scrapkit\NotificationKit\Domain\Templates\Models\NotificationTemplate;
use Scrapkit\NotificationKit\Http\Requests\ViewTemplateRequest;
use Scrapkit\NotificationKit\Http\Resources\TemplateVersionResource;

final class ListTemplateVersionsController
{
    public function __invoke(ViewTemplateRequest $request, NotificationTemplate $template): AnonymousResourceCollection
    {
        $versions = $template->versions()
            ->with('editedBy')
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->paginate($request->integer('per_page', 25));

        return TemplateVersionResource::collection($versions);
    }
}
