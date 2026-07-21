<?php

declare(strict_types=1);

arch('no debugging functions in production code')
    ->expect(['dd', 'dump', 'ray', 'var_dump'])
    ->each->not->toBeUsed();

arch('php preset')->preset()->php();

arch('security preset')->preset()->security();

arch('strict types everywhere')
    ->expect('Scrapkit\NotificationKit')
    ->toUseStrictTypes();

arch('the domain does not depend on the http or console layer')
    ->expect('Scrapkit\NotificationKit\Domain')
    ->not->toUse([
        'Scrapkit\NotificationKit\Http',
        'Scrapkit\NotificationKit\Console',
    ]);

arch('events are final')
    ->expect('Scrapkit\NotificationKit\Domain\Outbox\Events')
    ->toBeFinal();

arch('data transfer objects are readonly')
    ->expect('Scrapkit\NotificationKit\Domain\Templates\DataTransferObjects')
    ->toBeReadonly();
