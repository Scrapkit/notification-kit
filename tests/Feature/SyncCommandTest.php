<?php

declare(strict_types=1);

use Scrapkit\NotificationKit\Domain\Templates\Models\NotificationTemplate;

it('creates template rows with code defaults', function (): void {
    $this->artisan('notification-kit:sync')->assertSuccessful();

    $template = NotificationTemplate::query()->where('key', 'invoices.paid')->firstOrFail();

    expect($template)
        ->subject->toBeNull()
        ->body->toBeNull()
        ->default_subject->toBe('Your invoice {{ invoice.number }} is paid')
        ->requires_confirmation->toBeTrue()
        ->synced_at->not->toBeNull()
        ->and(NotificationTemplate::query()->count())->toBe(3);
});

it('never touches user-owned columns when re-syncing', function (): void {
    $this->artisan('notification-kit:sync')->assertSuccessful();

    NotificationTemplate::query()->where('key', 'invoices.paid')->firstOrFail()->update([
        'subject' => 'Custom subject',
        'body' => 'Custom body',
        'requires_confirmation' => false,
        'archived_at' => now(),
        'metadata' => ['color' => 'red'],
    ]);

    $this->artisan('notification-kit:sync')->assertSuccessful();

    $template = NotificationTemplate::query()->where('key', 'invoices.paid')->firstOrFail();

    expect($template)
        ->subject->toBe('Custom subject')
        ->body->toBe('Custom body')
        ->requires_confirmation->toBeFalse()
        ->archived_at->not->toBeNull()
        ->metadata->toBe(['color' => 'red'])
        ->default_subject->toBe('Your invoice {{ invoice.number }} is paid');
});

it('reports orphaned keys without deleting them', function (): void {
    NotificationTemplate::factory()->create(['key' => 'legacy.gone']);

    $this->artisan('notification-kit:sync')
        ->expectsOutputToContain('legacy.gone')
        ->assertSuccessful();

    expect(NotificationTemplate::query()->where('key', 'legacy.gone')->exists())->toBeTrue();
});
