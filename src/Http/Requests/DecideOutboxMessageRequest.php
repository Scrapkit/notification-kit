<?php

declare(strict_types=1);

namespace Scrapkit\NotificationKit\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Scrapkit\NotificationKit\Authorization\Ability;
use Scrapkit\NotificationKit\Authorization\NotificationKitGate;

/**
 * Approving and cancelling are the same decision from an authorization point
 * of view: both settle a pending message.
 */
final class DecideOutboxMessageRequest extends FormRequest
{
    public function authorize(): bool
    {
        return NotificationKitGate::allows($this->user(), Ability::Approve);
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [];
    }
}
