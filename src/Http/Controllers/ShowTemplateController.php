<?php

declare(strict_types=1);

namespace Scrapkit\NotificationKit\Http\Controllers;

use Scrapkit\NotificationKit\Domain\Templates\Models\NotificationTemplate;
use Scrapkit\NotificationKit\Http\Requests\ViewTemplateRequest;
use Scrapkit\NotificationKit\Http\Resources\TemplateResource;

final class ShowTemplateController
{
    public function __invoke(ViewTemplateRequest $request, NotificationTemplate $template): TemplateResource
    {
        return TemplateResource::make($template);
    }
}
