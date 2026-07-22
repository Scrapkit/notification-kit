<?php

declare(strict_types=1);

namespace Scrapkit\NotificationKit\Tests;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Orchestra\Testbench\TestCase as Orchestra;
use Scrapkit\NotificationKit\NotificationKitServiceProvider;
use Workbench\App\Mail\InvoicePaidMail;
use Workbench\App\Mail\WelcomeMail;
use Workbench\App\Models\User;
use Workbench\App\Notifications\InvoicePaidNotification;

class TestCase extends Orchestra
{
    protected function getPackageProviders($app)
    {
        return [
            NotificationKitServiceProvider::class,
        ];
    }

    public function getEnvironmentSetUp($app)
    {
        config()->set('app.key', 'base64:'.base64_encode(random_bytes(32)));
        config()->set('database.default', 'testing');
        config()->set('cache.default', 'array');
        config()->set('mail.default', 'array');
        config()->set('queue.default', 'sync');
        config()->set('session.driver', 'array');
        config()->set('auth.providers.users.model', User::class);
        config()->set('notification-kit.manageables', [
            WelcomeMail::class,
            InvoicePaidMail::class,
            InvoicePaidNotification::class,
        ]);
    }

    protected function defineDatabaseMigrations(): void
    {
        // The host application orders these by publish timestamp; here the
        // table has to exist before anything alters it.
        $stubs = glob(__DIR__.'/../database/migrations/*.php.stub') ?: [];

        usort($stubs, function (string $a, string $b): int {
            $creates = fn (string $path): int => str_starts_with(basename($path), 'create_') ? 0 : 1;

            return [$creates($a), $a] <=> [$creates($b), $b];
        });

        foreach ($stubs as $stub) {
            (include $stub)->up();
        }

        Schema::create('users', function (Blueprint $table): void {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->string('password');
            $table->timestamps();
        });
    }
}
