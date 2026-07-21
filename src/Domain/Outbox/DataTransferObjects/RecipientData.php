<?php

declare(strict_types=1);

namespace Scrapkit\NotificationKit\Domain\Outbox\DataTransferObjects;

final readonly class RecipientData
{
    public function __construct(
        public string $type,
        public string $address,
        public ?string $name = null,
    ) {}

    /**
     * @return array{type: string, address: string, name: ?string}
     */
    public function toArray(): array
    {
        return [
            'type' => $this->type,
            'address' => $this->address,
            'name' => $this->name,
        ];
    }
}
