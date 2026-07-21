<?php

declare(strict_types=1);

namespace Scrapkit\NotificationKit\Domain\Templates\Events;

use Illuminate\Queue\SerializesModels;
use Scrapkit\NotificationKit\Domain\Templates\Models\NotificationTemplate;

final class TemplateArchived
{
    use SerializesModels;

    public function __construct(public readonly NotificationTemplate $template) {}
}
