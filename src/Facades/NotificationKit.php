<?php

declare(strict_types=1);

namespace Scrapkit\NotificationKit\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @method static \Scrapkit\NotificationKit\PendingManagedMail to(mixed $recipients)
 * @method static \Scrapkit\NotificationKit\Domain\Outbox\DataTransferObjects\SendDispatch send(\Scrapkit\NotificationKit\Mail\ManagedMailable $mailable, \Scrapkit\NotificationKit\Domain\Outbox\DataTransferObjects\RecipientsSnapshot $recipients)
 * @method static \Scrapkit\NotificationKit\Domain\Outbox\Models\OutboxMessage approve(\Scrapkit\NotificationKit\Domain\Outbox\Models\OutboxMessage $message, ?\Illuminate\Contracts\Auth\Authenticatable $decidedBy = null)
 * @method static \Scrapkit\NotificationKit\Domain\Outbox\Models\OutboxMessage cancel(\Scrapkit\NotificationKit\Domain\Outbox\Models\OutboxMessage $message, ?\Illuminate\Contracts\Auth\Authenticatable $decidedBy = null)
 *
 * @see \Scrapkit\NotificationKit\NotificationKit
 */
final class NotificationKit extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return \Scrapkit\NotificationKit\NotificationKit::class;
    }
}
