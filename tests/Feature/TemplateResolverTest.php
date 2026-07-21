<?php

declare(strict_types=1);

use Scrapkit\NotificationKit\Domain\Templates\Enums\ContentSource;
use Scrapkit\NotificationKit\Domain\Templates\Models\NotificationTemplate;
use Scrapkit\NotificationKit\Domain\Templates\TemplateResolver;
use Workbench\App\Mail\InvoicePaidMail;

it('resolves database overrides', function (): void {
    $this->artisan('notification-kit:sync')->assertSuccessful();
    NotificationTemplate::query()->where('key', 'invoices.paid')->firstOrFail()->update([
        'subject' => 'Custom',
        'body' => 'Body {{ user.name }}',
    ]);

    $effective = app(TemplateResolver::class)->resolve(InvoicePaidMail::template());

    expect($effective->subject)->toBe('Custom')
        ->and($effective->body)->toBe('Body {{ user.name }}')
        ->and($effective->source)->toBe(ContentSource::Database)
        ->and($effective->requiresConfirmation)->toBeTrue()
        ->and($effective->archived)->toBeFalse();
});

it('falls back to code defaults when the row is missing', function (): void {
    $effective = app(TemplateResolver::class)->resolve(InvoicePaidMail::template());

    expect($effective->source)->toBe(ContentSource::CodeDefault)
        ->and($effective->subject)->toBe(InvoicePaidMail::template()->defaultSubject)
        ->and($effective->body)->toBe(InvoicePaidMail::template()->defaultBody)
        ->and($effective->requiresConfirmation)->toBeTrue();
});

it('caches lookups until forgotten', function (): void {
    $this->artisan('notification-kit:sync')->assertSuccessful();
    $resolver = app(TemplateResolver::class);
    $template = NotificationTemplate::query()->where('key', 'invoices.paid')->firstOrFail();

    $template->update(['subject' => 'First']);
    expect($resolver->resolve(InvoicePaidMail::template())->subject)->toBe('First');

    $template->update(['subject' => 'Second']);
    expect($resolver->resolve(InvoicePaidMail::template())->subject)->toBe('First');

    $resolver->forget('invoices.paid');
    expect($resolver->resolve(InvoicePaidMail::template())->subject)->toBe('Second');
});
