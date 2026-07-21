<?php

declare(strict_types=1);

namespace Scrapkit\NotificationKit\Support;

use Illuminate\Database\Eloquent\Model;

/**
 * Renders the audit morph relations without knowing the host's user model.
 */
final class UserDisplay
{
    public static function name(?Model $user): ?string
    {
        $value = $user?->getAttribute(KitConfig::userDisplayAttribute());

        return is_string($value) ? $value : null;
    }
}
