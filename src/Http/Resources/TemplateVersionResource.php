<?php

declare(strict_types=1);

namespace Scrapkit\NotificationKit\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Scrapkit\NotificationKit\Domain\Templates\Models\TemplateVersion;
use Scrapkit\NotificationKit\Support\UserDisplay;

/**
 * @mixin TemplateVersion
 */
final class TemplateVersionResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'subject' => $this->subject,
            'body' => $this->body,
            'metadata' => $this->metadata ?? [],
            'requires_confirmation' => $this->requires_confirmation,
            'edited_by' => UserDisplay::name($this->editedBy),
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}
