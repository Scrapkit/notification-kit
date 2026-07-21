<?php

declare(strict_types=1);

namespace Scrapkit\NotificationKit\Domain\Templates\DataTransferObjects;

final readonly class SyncResult
{
    /**
     * @param  list<string>  $created
     * @param  list<string>  $updated
     * @param  list<string>  $orphaned
     */
    public function __construct(
        public array $created,
        public array $updated,
        public array $orphaned,
    ) {}
}
