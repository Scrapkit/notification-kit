<?php

declare(strict_types=1);

namespace Scrapkit\NotificationKit\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Scrapkit\NotificationKit\Authorization\Ability;
use Scrapkit\NotificationKit\Authorization\NotificationKitGate;
use Scrapkit\NotificationKit\Domain\Templates\DataTransferObjects\TemplateContentData;

final class UpdateTemplateContentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return NotificationKitGate::allows($this->user(), Ability::UpdateContent);
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'subject' => ['present', 'nullable', 'string', 'max:255'],
            'body' => ['present', 'nullable', 'string', 'max:65000'],
            'metadata' => ['sometimes', 'array'],
            'requires_confirmation' => ['required', 'boolean'],
        ];
    }

    public function toData(): TemplateContentData
    {
        /** @var array<string, mixed> $metadata */
        $metadata = $this->validated('metadata', []);

        return new TemplateContentData(
            subject: $this->string('subject')->toString() !== '' ? $this->string('subject')->toString() : null,
            body: $this->string('body')->toString() !== '' ? $this->string('body')->toString() : null,
            metadata: $metadata,
            requiresConfirmation: $this->boolean('requires_confirmation'),
        );
    }
}
