<?php

declare(strict_types=1);

namespace Scrapkit\NotificationKit\Http\Controllers;

use Scrapkit\NotificationKit\Domain\Templates\Actions\ArchiveTemplateAction;
use Scrapkit\NotificationKit\Domain\Templates\Models\NotificationTemplate;
use Scrapkit\NotificationKit\Http\Requests\ArchiveTemplateRequest;
use Scrapkit\NotificationKit\Http\Resources\TemplateResource;

final class ArchiveTemplateController
{
    public function __invoke(
        ArchiveTemplateRequest $request,
        NotificationTemplate $template,
        ArchiveTemplateAction $action,
    ): TemplateResource {
        return TemplateResource::make($action->execute($template, archived: true));
    }
}
