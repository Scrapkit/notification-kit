<?php

declare(strict_types=1);

namespace Scrapkit\NotificationKit\Http\Controllers;

use Scrapkit\NotificationKit\Domain\Templates\Actions\UpdateTemplateContentAction;
use Scrapkit\NotificationKit\Domain\Templates\Models\NotificationTemplate;
use Scrapkit\NotificationKit\Http\Requests\UpdateTemplateContentRequest;
use Scrapkit\NotificationKit\Http\Resources\TemplateResource;

final class UpdateTemplateContentController
{
    public function __invoke(
        UpdateTemplateContentRequest $request,
        NotificationTemplate $template,
        UpdateTemplateContentAction $action,
    ): TemplateResource {
        return TemplateResource::make($action->execute($template, $request->toData(), $request->user()));
    }
}
