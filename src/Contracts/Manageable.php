<?php

declare(strict_types=1);

namespace Scrapkit\NotificationKit\Contracts;

use Scrapkit\NotificationKit\Domain\Templates\DataTransferObjects\TemplateDefinition;

/**
 * A host Mailable or Notification whose content is managed by the kit.
 *
 * The method is static so the sync command can read definitions without
 * constructing the class (constructors usually take domain models).
 */
interface Manageable
{
    public static function template(): TemplateDefinition;
}
