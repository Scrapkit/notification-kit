<?php

declare(strict_types=1);

namespace Scrapkit\NotificationKit\Domain\Templates\Events;

use Illuminate\Queue\SerializesModels;
use Scrapkit\NotificationKit\Domain\Templates\Models\NotificationTemplate;
use Scrapkit\NotificationKit\Domain\Templates\Models\TemplateVersion;

final class TemplateContentUpdated
{
    use SerializesModels;

    public function __construct(
        public readonly NotificationTemplate $template,
        public readonly TemplateVersion $version,
    ) {}
}
