<?php

declare(strict_types=1);

namespace Scrapkit\NotificationKit\Domain\Templates\DataTransferObjects;

use Scrapkit\NotificationKit\Domain\Templates\Enums\ContentSource;

/**
 * The content a template resolves to at send time: database overrides when
 * available, code defaults otherwise.
 */
final readonly class EffectiveTemplate
{
    /**
     * @param  array<string, mixed>  $metadata
     */
    public function __construct(
        public string $key,
        public ?string $subject,
        public string $body,
        public bool $requiresConfirmation,
        public array $metadata,
        public ContentSource $source,
        public bool $archived,
    ) {}
}
