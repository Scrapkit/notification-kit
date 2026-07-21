<?php

declare(strict_types=1);

namespace Scrapkit\NotificationKit\Domain\Rendering;

final readonly class RenderedContent
{
    /**
     * @param  list<string>  $missingPlaceholders
     */
    public function __construct(
        public ?string $subject,
        public string $bodyHtml,
        public array $missingPlaceholders = [],
    ) {}
}
