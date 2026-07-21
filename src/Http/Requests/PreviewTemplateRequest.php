<?php

declare(strict_types=1);

namespace Scrapkit\NotificationKit\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Scrapkit\NotificationKit\Authorization\Ability;
use Scrapkit\NotificationKit\Authorization\NotificationKitGate;

final class PreviewTemplateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return NotificationKitGate::allows($this->user(), Ability::View);
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'subject' => ['sometimes', 'nullable', 'string', 'max:255'],
            'body' => ['sometimes', 'nullable', 'string', 'max:65000'],
            'sample_data' => ['sometimes', 'array'],
        ];
    }
}
