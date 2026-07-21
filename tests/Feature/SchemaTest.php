<?php

declare(strict_types=1);

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * The suite runs on SQLite, which has no identifier length limit. MySQL and
 * MariaDB cap identifiers at 64 characters, so a generated index name that is
 * fine here would break `migrate` on a host application.
 */
it('keeps every index name within the 64 character limit of MySQL', function (): void {
    $names = DB::table('sqlite_master')
        ->where('type', 'index')
        ->whereNotNull('name')
        ->where('tbl_name', 'like', 'notification_kit_%')
        ->pluck('name')
        ->reject(fn (string $name): bool => str_starts_with($name, 'sqlite_autoindex'));

    expect($names)->not->toBeEmpty();

    foreach ($names as $name) {
        expect(strlen($name))->toBeLessThanOrEqual(64, "index [{$name}] is too long for MySQL");
    }
});

it('creates every table the package needs', function (): void {
    expect(Schema::hasTable('notification_kit_templates'))->toBeTrue()
        ->and(Schema::hasTable('notification_kit_template_versions'))->toBeTrue()
        ->and(Schema::hasTable('notification_kit_outbox_messages'))->toBeTrue();
});
