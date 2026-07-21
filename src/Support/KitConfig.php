<?php

declare(strict_types=1);

namespace Scrapkit\NotificationKit\Support;

/**
 * Typed accessors for the package configuration.
 */
final class KitConfig
{
    public static function tablePrefix(): string
    {
        $prefix = config('notification-kit.database.table_prefix', 'notification_kit_');

        return is_string($prefix) ? $prefix : 'notification_kit_';
    }

    public static function connection(): ?string
    {
        $connection = config('notification-kit.database.connection');

        return is_string($connection) ? $connection : null;
    }

    public static function routePrefix(): string
    {
        $prefix = config('notification-kit.routes.prefix', 'notification-kit/api/v1');

        return is_string($prefix) ? $prefix : 'notification-kit/api/v1';
    }

    /**
     * @return list<string>
     */
    public static function routeMiddleware(): array
    {
        $middleware = config('notification-kit.routes.middleware', ['web', 'auth']);

        return is_array($middleware) ? array_values(array_filter($middleware, 'is_string')) : ['web', 'auth'];
    }

    /**
     * @return list<class-string>
     */
    public static function manageables(): array
    {
        $classes = config('notification-kit.manageables', []);

        /** @var list<class-string> */
        return is_array($classes) ? array_values(array_filter($classes, 'is_string')) : [];
    }

    public static function cacheStore(): ?string
    {
        $store = config('notification-kit.cache.store');

        return is_string($store) ? $store : null;
    }

    public static function cacheTtl(): int
    {
        $ttl = config('notification-kit.cache.ttl', 300);

        return is_int($ttl) ? $ttl : 300;
    }

    public static function queueConnection(): ?string
    {
        $connection = config('notification-kit.queue.connection');

        return is_string($connection) ? $connection : null;
    }

    public static function queueName(): ?string
    {
        $queue = config('notification-kit.queue.queue');

        return is_string($queue) ? $queue : null;
    }

    public static function queueTries(): int
    {
        $tries = config('notification-kit.queue.tries', 3);

        return is_int($tries) ? $tries : 3;
    }

    public static function queueBackoff(): int
    {
        $backoff = config('notification-kit.queue.backoff', 60);

        return is_int($backoff) ? $backoff : 60;
    }

    public static function missingPlaceholderPolicy(): string
    {
        $policy = config('notification-kit.placeholders.missing', 'empty');

        return $policy === 'keep' ? 'keep' : 'empty';
    }

    public static function userDisplayAttribute(): string
    {
        $attribute = config('notification-kit.users.display_attribute', 'name');

        return is_string($attribute) ? $attribute : 'name';
    }
}
