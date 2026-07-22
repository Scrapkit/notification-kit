<?php

declare(strict_types=1);

namespace Scrapkit\NotificationKit;

use Illuminate\Support\Facades\Route;
use Scrapkit\NotificationKit\Console\SyncTemplatesCommand;
use Scrapkit\NotificationKit\Domain\Rendering\TemplateRenderer;
use Scrapkit\NotificationKit\Domain\Templates\TemplateResolver;
use Scrapkit\NotificationKit\Http\Middleware\AuthorizeNotificationKit;
use Scrapkit\NotificationKit\Support\KitConfig;
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
                'add_supports_confirmation_to_notification_kit_templates_table',
            ])
            ->hasCommand(SyncTemplatesCommand::class);
    }

    public function packageRegistered(): void
    {
        $this->app->singleton(TemplateResolver::class);
        $this->app->singleton(TemplateRenderer::class);
        $this->app->singleton(NotificationKit::class);
    }

    public function packageBooted(): void
    {
        $this->registerRoutes();
        $this->registerPublishableStubs();
    }

    private function registerRoutes(): void
    {
        Route::prefix(KitConfig::routePrefix())
            ->middleware([...KitConfig::routeMiddleware(), AuthorizeNotificationKit::class])
            ->group(fn () => $this->loadRoutesFrom(__DIR__.'/../routes/api.php'));
    }

    /**
     * The React pages and components are scaffolding: once published they
     * belong to the host application and package upgrades leave them alone.
     */
    private function registerPublishableStubs(): void
    {
        $this->publishes([
            __DIR__.'/../resources/js' => resource_path('js'),
        ], 'notification-kit-stubs');
    }
}
