<?php

declare(strict_types=1);

namespace Scrapkit\NotificationKit;

use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

final class NotificationKitServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        $package
            ->name('notification-kit')
            ->hasConfigFile()
            ->hasMigrations([
                'create_notification_kit_templates_table',
                'create_notification_kit_template_versions_table',
                'create_notification_kit_outbox_messages_table',
            ]);
    }
}
