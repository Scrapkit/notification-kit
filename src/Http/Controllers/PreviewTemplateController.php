<?php

declare(strict_types=1);

namespace Scrapkit\NotificationKit\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Scrapkit\NotificationKit\Domain\Templates\Actions\PreviewTemplateAction;
use Scrapkit\NotificationKit\Domain\Templates\Models\NotificationTemplate;
use Scrapkit\NotificationKit\Http\Requests\PreviewTemplateRequest;

final class PreviewTemplateController
{
    public function __invoke(
        PreviewTemplateRequest $request,
        NotificationTemplate $template,
        PreviewTemplateAction $action,
    ): JsonResponse {
        /** @var ?array<string, mixed> $sampleData */
        $sampleData = $request->validated('sample_data');

        $content = $action->execute(
            $template,
            $request->has('subject') ? $request->string('subject')->toString() : null,
            $request->has('body') ? $request->string('body')->toString() : null,
            $sampleData,
        );

        return new JsonResponse([
            'data' => [
                'subject' => $content->subject,
                'body_html' => $content->bodyHtml,
                'missing_placeholders' => $content->missingPlaceholders,
            ],
        ]);
    }
}
