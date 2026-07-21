<?php

declare(strict_types=1);

namespace Scrapkit\NotificationKit\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Scrapkit\NotificationKit\Authorization\Ability;
use Scrapkit\NotificationKit\Authorization\NotificationKitGate;

final class ArchiveTemplateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return NotificationKitGate::allows($this->user(), Ability::Archive);
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [];
    }
}
