<?php

declare(strict_types=1);

namespace Scrapkit\NotificationKit\Domain\Templates\Actions;

use Illuminate\Support\Facades\Event;
use Scrapkit\NotificationKit\Domain\Templates\Events\TemplateArchived;
use Scrapkit\NotificationKit\Domain\Templates\Events\TemplateUnarchived;
use Scrapkit\NotificationKit\Domain\Templates\Models\NotificationTemplate;
use Scrapkit\NotificationKit\Domain\Templates\TemplateResolver;

/**
 * Archiving hides a template from the default listing. It is never a delete:
 * the row, its history and its outbox messages stay.
 */
final class ArchiveTemplateAction
{
    public function __construct(private readonly TemplateResolver $resolver) {}

    public function execute(NotificationTemplate $template, bool $archived): NotificationTemplate
    {
        $template->update(['archived_at' => $archived ? now() : null]);

        $this->resolver->forget($template->key);

        Event::dispatch($archived ? new TemplateArchived($template) : new TemplateUnarchived($template));

        return $template;
    }
}
