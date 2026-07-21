<?php

declare(strict_types=1);

namespace Scrapkit\NotificationKit\Domain\Templates\DataTransferObjects;

/**
 * The user-owned configuration of a template. A null subject or body means
 * "fall back to the default shipped in code".
 */
final readonly class TemplateContentData
{
    /**
     * @param  array<string, mixed>  $metadata
     */
    public function __construct(
        public ?string $subject,
        public ?string $body,
        public array $metadata,
        public bool $requiresConfirmation,
    ) {}
}
