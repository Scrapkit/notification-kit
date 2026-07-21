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
            ->hasConfigFile();
    }
}
