<?php

declare(strict_types=1);

use Illuminate\Support\ServiceProvider;

it('publishes the frontend stubs under their own tag', function (): void {
    $paths = ServiceProvider::pathsToPublish(null, 'notification-kit-stubs');

    expect($paths)->not->toBeEmpty()
        ->and(array_key_first($paths))->toEndWith('resources/js');
});

it('ships every stub the docs mention', function (): void {
    $root = __DIR__.'/../../resources/js';

    $expected = [
        'lib/notification-kit/api.ts',
        'lib/notification-kit/types.ts',
        'components/notification-kit/ConfirmSendModal.tsx',
        'components/notification-kit/PlaceholderPalette.tsx',
        'components/notification-kit/TemplatePreview.tsx',
        'components/notification-kit/OutboxStatusBadge.tsx',
        'pages/notification-kit/templates/index.tsx',
        'pages/notification-kit/templates/edit.tsx',
        'pages/notification-kit/outbox/index.tsx',
    ];

    foreach ($expected as $stub) {
        expect(file_exists("{$root}/{$stub}"))->toBeTrue("missing stub [{$stub}]");
    }
});
