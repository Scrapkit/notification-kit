<?php

declare(strict_types=1);

namespace Scrapkit\NotificationKit\Domain\Templates\Enums;

/**
 * Where the effective content of a template came from.
 */
enum ContentSource: string
{
    case Database = 'database';
    case CodeDefault = 'code_default';
}
