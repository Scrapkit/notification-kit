<?php

declare(strict_types=1);

namespace Scrapkit\NotificationKit\Tests;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Orchestra\Testbench\TestCase as Orchestra;
use Scrapkit\NotificationKit\NotificationKitServiceProvider;

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
    }

    protected function defineDatabaseMigrations(): void
    {
        foreach (glob(__DIR__.'/../database/migrations/*.php.stub') ?: [] as $stub) {
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
