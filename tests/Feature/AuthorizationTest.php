<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Gate;
use Scrapkit\NotificationKit\Domain\Outbox\Models\OutboxMessage;
use Scrapkit\NotificationKit\Domain\Templates\Models\NotificationTemplate;
use Workbench\App\Models\User;

beforeEach(function (): void {
    $this->user = User::query()->create(['name' => 'Bob', 'email' => 'bob@example.com', 'password' => 'x']);
});

it('denies everything when the host defines no gate', function (): void {
    $this->actingAs($this->user)->getJson('notification-kit/api/v1/templates')->assertForbidden();
    $this->actingAs($this->user)->getJson('notification-kit/api/v1/outbox')->assertForbidden();
});

it('denies guests', function (): void {
    Gate::define('viewNotificationKit', fn (): bool => true);

    $this->getJson('notification-kit/api/v1/templates')->assertUnauthorized();
});

it('grants every ability through the entry gate', function (): void {
    Gate::define('viewNotificationKit', fn (): bool => true);
    $template = NotificationTemplate::factory()->create();

    $this->actingAs($this->user)->getJson('notification-kit/api/v1/templates')->assertOk();
    $this->actingAs($this->user)
        ->putJson("notification-kit/api/v1/templates/{$template->key}/content", [
            'subject' => 'Edited',
            'body' => 'Body',
            'requires_confirmation' => false,
        ])
        ->assertOk();
});

it('lets a host deny a single ability without denying the rest', function (): void {
    Gate::define('viewNotificationKit', fn (): bool => true);
    Gate::define('notification-kit.approve', fn (): bool => false);

    $template = NotificationTemplate::factory()->create();
    $message = OutboxMessage::factory()->create();

    $this->actingAs($this->user)
        ->putJson("notification-kit/api/v1/templates/{$template->key}/content", [
            'subject' => 'Edited',
            'body' => 'Body',
            'requires_confirmation' => false,
        ])
        ->assertOk();

    $this->actingAs($this->user)
        ->postJson("notification-kit/api/v1/outbox/{$message->uuid}/approve")
        ->assertForbidden();
});
