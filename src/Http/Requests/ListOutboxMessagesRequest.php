<?php

declare(strict_types=1);

namespace Scrapkit\NotificationKit\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Scrapkit\NotificationKit\Authorization\Ability;
use Scrapkit\NotificationKit\Authorization\NotificationKitGate;
use Scrapkit\NotificationKit\Domain\Outbox\Enums\OutboxStatus;

final class ListOutboxMessagesRequest extends FormRequest
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
            'status' => ['sometimes', Rule::enum(OutboxStatus::class)],
            'template_key' => ['sometimes', 'string', 'max:150'],
            'search' => ['sometimes', 'string', 'max:150'],
            'per_page' => ['sometimes', 'integer', 'min:1', 'max:100'],
        ];
    }
}
