<?php

declare(strict_types=1);

namespace Scrapkit\NotificationKit\Authorization;

use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Support\Facades\Gate;

/**
 * Authorization for the package UI, in the style of Horizon.
 *
 * The host defines the `viewNotificationKit` gate; the package defines no
 * default, so an app that never configures it denies everyone. Fine-grained
 * abilities are optional: when a host does not define one, the entry gate
 * decides.
 */
final class NotificationKitGate
{
    public const string ENTRY_GATE = 'viewNotificationKit';

    public static function allows(?Authenticatable $user, Ability $ability): bool
    {
        if ($user === null) {
            return false;
        }

        $gate = Gate::forUser($user);

        if ($gate->has($ability->gateName())) {
            return $gate->allows($ability->gateName());
        }

        return $gate->has(self::ENTRY_GATE) && $gate->allows(self::ENTRY_GATE);
    }

    public static function allowsEntry(?Authenticatable $user): bool
    {
        if ($user === null) {
            return false;
        }

        $gate = Gate::forUser($user);

        return $gate->has(self::ENTRY_GATE) && $gate->allows(self::ENTRY_GATE);
    }
}
