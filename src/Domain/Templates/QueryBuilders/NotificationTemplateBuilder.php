<?php

declare(strict_types=1);

namespace Scrapkit\NotificationKit\Domain\Templates\QueryBuilders;

use Illuminate\Database\Eloquent\Builder;
use Scrapkit\NotificationKit\Domain\Templates\Enums\TemplateType;
use Scrapkit\NotificationKit\Domain\Templates\Models\NotificationTemplate;

/**
 * @extends Builder<NotificationTemplate>
 */
final class NotificationTemplateBuilder extends Builder
{
    public function ofType(TemplateType $type): self
    {
        return $this->where('type', $type->value);
    }

    public function withoutArchived(): self
    {
        return $this->whereNull('archived_at');
    }

    public function onlyArchived(): self
    {
        return $this->whereNotNull('archived_at');
    }

    public function requiresConfirmation(bool $required = true): self
    {
        return $this->where('requires_confirmation', $required);
    }

    public function search(string $term): self
    {
        return $this->where(function (self $query) use ($term): void {
            $query->where('key', 'like', "%{$term}%")
                ->orWhere('name', 'like', "%{$term}%");
        });
    }

    public function ordered(): self
    {
        return $this->orderBy('key');
    }
}
