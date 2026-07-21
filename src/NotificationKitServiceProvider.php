<?php

declare(strict_types=1);

namespace Scrapkit\NotificationKit;

use Scrapkit\NotificationKit\Console\SyncTemplatesCommand;
use Scrapkit\NotificationKit\Domain\Rendering\TemplateRenderer;
use Scrapkit\NotificationKit\Domain\Templates\TemplateResolver;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

final class NotificationKitServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        $package
            ->name('notification-kit')
            ->hasConfigFile()
            ->hasViews()
            ->hasMigrations([
                'create_notification_kit_templates_table',
                'create_notification_kit_template_versions_table',
                'create_notification_kit_outbox_messages_table',
            ])
            ->hasCommand(SyncTemplatesCommand::class);
    }

    public function packageRegistered(): void
    {
        $this->app->singleton(TemplateResolver::class);
        $this->app->singleton(TemplateRenderer::class);
    }
}
