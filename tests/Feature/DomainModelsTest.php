<?php

declare(strict_types=1);

use Scrapkit\NotificationKit\Domain\Outbox\Models\OutboxMessage;
use Scrapkit\NotificationKit\Domain\Templates\Enums\TemplateType;
use Scrapkit\NotificationKit\Domain\Templates\Models\NotificationTemplate;
use Scrapkit\NotificationKit\Domain\Templates\Models\TemplateVersion;

it('prefixes every table from config', function (): void {
    expect((new NotificationTemplate)->getTable())->toBe('notification_kit_templates')
        ->and((new TemplateVersion)->getTable())->toBe('notification_kit_template_versions')
        ->and((new OutboxMessage)->getTable())->toBe('notification_kit_outbox_messages');
});

it('creates templates through the factory with array casts', function (): void {
    $template = NotificationTemplate::factory()->create([
        'placeholders' => [['key' => 'user.name', 'description' => 'Name', 'example' => 'Ada']],
    ]);

    expect($template->refresh())
        ->type->toBeInstanceOf(TemplateType::class)
        ->placeholders->toBeArray()
        ->requires_confirmation->toBeFalse()
        ->archived_at->toBeNull();
});

it('falls back to code defaults when overrides are null', function (): void {
    $template = NotificationTemplate::factory()->create([
        'subject' => null,
        'body' => null,
        'default_subject' => 'Default subject',
        'default_body' => 'Default body',
    ]);

    expect($template->effectiveSubject())->toBe('Default subject')
        ->and($template->effectiveBody())->toBe('Default body')
        ->and($template->isCustomized())->toBeFalse();

    $template->update(['subject' => 'Custom']);

    expect($template->effectiveSubject())->toBe('Custom')
        ->and($template->isCustomized())->toBeTrue();
});

it('filters and searches templates through the custom builder', function (): void {
    NotificationTemplate::factory()->email()->create(['key' => 'invoices.paid', 'name' => 'Invoice paid']);
    NotificationTemplate::factory()->notification()->create(['key' => 'users.suspended', 'name' => 'User suspended']);
    NotificationTemplate::factory()->email()->archived()->create(['key' => 'legacy.newsletter', 'name' => 'Legacy newsletter']);

    expect(NotificationTemplate::query()->ofType(TemplateType::Email)->pluck('key')->all())
        ->toBe(['invoices.paid', 'legacy.newsletter'])
        ->and(NotificationTemplate::query()->withoutArchived()->pluck('key')->all())
        ->toBe(['invoices.paid', 'users.suspended'])
        ->and(NotificationTemplate::query()->onlyArchived()->pluck('key')->all())
        ->toBe(['legacy.newsletter'])
        ->and(NotificationTemplate::query()->search('invoice')->pluck('key')->all())
        ->toBe(['invoices.paid']);
});

it('keeps version rows append-only without updated_at', function (): void {
    $version = TemplateVersion::factory()->create();

    expect($version->updated_at ?? null)->toBeNull()
        ->and($version->created_at)->not->toBeNull();
});

it('generates a uuid route key for outbox messages', function (): void {
    $message = OutboxMessage::factory()->create();

    expect($message->uuid)->toBeUuid()
        ->and($message->getRouteKey())->toBe($message->uuid)
        ->and((new OutboxMessage)->getRouteKeyName())->toBe('uuid');
});
