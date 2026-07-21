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
