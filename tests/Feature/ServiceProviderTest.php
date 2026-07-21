<?php

declare(strict_types=1);

it('registers the package config', function (): void {
    expect(config('notification-kit.routes.prefix'))->toBe('notification-kit/api/v1')
        ->and(config('notification-kit.database.table_prefix'))->toBe('notification_kit_')
        ->and(config('notification-kit.cache.ttl'))->toBe(300);
});
