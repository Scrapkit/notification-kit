<?php

declare(strict_types=1);

namespace Scrapkit\NotificationKit\Domain\Templates\DataTransferObjects;

final readonly class PlaceholderDefinition
{
    public function __construct(
        public string $key,
        public string $description,
        public ?string $example = null,
    ) {}
}
