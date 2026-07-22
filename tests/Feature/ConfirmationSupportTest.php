<?php

declare(strict_types=1);

use Illuminate\Foundation\Auth\User;
use Illuminate\Support\Facades\Gate;
use Scrapkit\NotificationKit\Domain\Templates\Models\NotificationTemplate;

/**
 * Only a ManagedMailable sent through the kit can be held for approval.
 * A Notification is dispatched by the host with notify(), which never reaches
 * the pipeline, so offering the confirmation flag there would be a lie.
 */
beforeEach(function (): void {
    Gate::define('viewNotificationKit', fn (): bool => true);
    $this->artisan('notification-kit:sync')->assertSuccessful();
});

it('marks mailables as supporting confirmation and notifications as not', function (): void {
    $supports = fn (string $key): bool => (bool) NotificationTemplate::query()
        ->where('key', $key)
        ->value('supports_confirmation');

    expect($supports('invoices.paid'))->toBeTrue()
        ->and($supports('users.welcome'))->toBeTrue()
        ->and($supports('invoices.paid_notification'))->toBeFalse();
});

it('exposes the flag through the api', function (): void {
    $this->actingAs(new User)
        ->getJson('notification-kit/api/v1/templates/invoices.paid_notification')
        ->assertOk()
        ->assertJsonPath('data.supports_confirmation', false);
});

it('refuses to require confirmation on a template that cannot be held', function (): void {
    $this->actingAs(new User)
        ->putJson('notification-kit/api/v1/templates/invoices.paid_notification/content', [
            'subject' => 'Titolo',
            'body' => 'Corpo',
            'requires_confirmation' => true,
        ])
        ->assertStatus(422)
        ->assertJsonValidationErrors('requires_confirmation');

    expect((bool) NotificationTemplate::query()->where('key', 'invoices.paid_notification')->value('requires_confirmation'))
        ->toBeFalse();
});

it('still allows requiring confirmation on a managed mailable', function (): void {
    $this->actingAs(new User)
        ->putJson('notification-kit/api/v1/templates/users.welcome/content', [
            'subject' => 'Benvenuto',
            'body' => 'Ciao',
            'requires_confirmation' => true,
        ])
        ->assertOk()
        ->assertJsonPath('data.requires_confirmation', true);
});
