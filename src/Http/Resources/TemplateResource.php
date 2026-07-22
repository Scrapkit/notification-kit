<?php

declare(strict_types=1);

namespace Scrapkit\NotificationKit\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Scrapkit\NotificationKit\Domain\Templates\Models\NotificationTemplate;

/**
 * @mixin NotificationTemplate
 */
final class TemplateResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'key' => $this->key,
            'type' => $this->type->value,
            'name' => $this->name,
            'description' => $this->description,
            'subject' => $this->subject,
            'body' => $this->body,
            'default_subject' => $this->default_subject,
            'default_body' => $this->default_body,
            'is_customized' => $this->isCustomized(),
            'placeholders' => $this->placeholders,
            'sample_data' => $this->sample_data,
            'metadata' => $this->metadata ?? [],
            'requires_confirmation' => $this->requires_confirmation,
            'supports_confirmation' => $this->supports_confirmation,
            'archived_at' => $this->archived_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
