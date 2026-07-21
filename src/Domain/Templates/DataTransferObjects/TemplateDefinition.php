<?php

declare(strict_types=1);

namespace Scrapkit\NotificationKit\Domain\Templates\DataTransferObjects;

use Scrapkit\NotificationKit\Domain\Templates\Enums\TemplateType;

final readonly class TemplateDefinition
{
    /**
     * @param  list<PlaceholderDefinition>  $placeholders
     * @param  array<string, mixed>  $sampleData
     * @param  array<string, mixed>  $metadata
     */
    public function __construct(
        public string $key,
        public TemplateType $type,
        public string $name,
        public ?string $description = null,
        public ?string $defaultSubject = null,
        public string $defaultBody = '',
        public array $placeholders = [],
        public array $sampleData = [],
        public bool $requiresConfirmation = false,
        public array $metadata = [],
    ) {}

    /**
     * @return array<int, array{key: string, description: string, example: ?string}>
     */
    public function placeholdersToArray(): array
    {
        return array_map(
            static fn (PlaceholderDefinition $placeholder): array => [
                'key' => $placeholder->key,
                'description' => $placeholder->description,
                'example' => $placeholder->example,
            ],
            $this->placeholders,
        );
    }
}
