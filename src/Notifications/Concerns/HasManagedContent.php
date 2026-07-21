<?php

declare(strict_types=1);

namespace Scrapkit\NotificationKit\Notifications\Concerns;

use Scrapkit\NotificationKit\Domain\Rendering\RenderedContent;
use Scrapkit\NotificationKit\Domain\Rendering\TemplateRenderer;
use Scrapkit\NotificationKit\Domain\Templates\DataTransferObjects\EffectiveTemplate;
use Scrapkit\NotificationKit\Domain\Templates\TemplateResolver;

/**
 * Gives a host Notification editable content. The class must also implement
 * Scrapkit\NotificationKit\Contracts\Manageable.
 */
trait HasManagedContent
{
    /**
     * @param  array<string, mixed>  $data
     */
    protected function renderManaged(array $data): RenderedContent
    {
        $effective = $this->effectiveManagedTemplate();

        return app(TemplateRenderer::class)->render($effective->subject, $effective->body, $data);
    }

    protected function effectiveManagedTemplate(): EffectiveTemplate
    {
        return app(TemplateResolver::class)->resolve(static::template());
    }
}
