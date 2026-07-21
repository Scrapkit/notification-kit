<?php

declare(strict_types=1);

namespace Scrapkit\NotificationKit\Domain\Templates\Enums;

enum TemplateType: string
{
    case Email = 'email';
    case Notification = 'notification';
}
