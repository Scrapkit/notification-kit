<?php

declare(strict_types=1);

namespace Scrapkit\NotificationKit\Domain\Templates\Actions;

use Scrapkit\NotificationKit\Domain\Rendering\RenderedContent;
use Scrapkit\NotificationKit\Domain\Rendering\TemplateRenderer;
use Scrapkit\NotificationKit\Domain\Templates\Models\NotificationTemplate;

/**
 * Renders stored or draft content against the template's sample data so the
 * editor can show a live preview.
 */
final class PreviewTemplateAction
{
    public function __construct(private readonly TemplateRenderer $renderer) {}

    /**
     * @param  ?array<string, mixed>  $sampleData
     */
    public function execute(
        NotificationTemplate $template,
        ?string $draftSubject = null,
        ?string $draftBody = null,
        ?array $sampleData = null,
    ): RenderedContent {
        return $this->renderer->render(
            $draftSubject ?? $template->effectiveSubject(),
            $draftBody ?? $template->effectiveBody(),
            $sampleData ?? $template->sample_data,
        );
    }
}
