<?php

declare(strict_types=1);

use Scrapkit\NotificationKit\Domain\Rendering\TemplateRenderer;

it('renders markdown and resolves placeholders', function (): void {
    $content = app(TemplateRenderer::class)->render(
        'Invoice {{ invoice.number }}',
        'Hi **{{ user.name }}**, invoice {{ invoice.number }} is paid.',
        ['user' => ['name' => 'Ada'], 'invoice' => ['number' => 'INV-1']],
    );

    expect($content->subject)->toBe('Invoice INV-1')
        ->and($content->bodyHtml)->toContain('<strong>Ada</strong>')
        ->and($content->bodyHtml)->toContain('INV-1')
        ->and($content->missingPlaceholders)->toBe([]);
});

it('escapes html in placeholder values', function (): void {
    $content = app(TemplateRenderer::class)->render(
        null,
        'Hi {{ user.name }}',
        ['user' => ['name' => '<script>alert(1)</script>']],
    );

    expect($content->bodyHtml)->not->toContain('<script>')
        ->and($content->bodyHtml)->toContain('&lt;script&gt;');
});

it('escapes raw html written in the body markdown', function (): void {
    $content = app(TemplateRenderer::class)->render(null, 'before <b onclick="x()">bold</b> after', []);

    expect($content->bodyHtml)->not->toContain('<b onclick');
});

it('strips unsafe links', function (): void {
    $content = app(TemplateRenderer::class)->render(null, '[click](javascript:alert(1))', []);

    expect($content->bodyHtml)->not->toContain('javascript:alert');
});

it('blanks missing placeholders by default and reports them', function (): void {
    $content = app(TemplateRenderer::class)->render(null, 'Hello {{ unknown.key }}!', []);

    expect($content->bodyHtml)->not->toContain('unknown.key')
        ->and($content->missingPlaceholders)->toBe(['unknown.key']);
});

it('keeps missing placeholders when configured to', function (): void {
    config()->set('notification-kit.placeholders.missing', 'keep');

    $content = app(TemplateRenderer::class)->render(null, 'Hello {{ unknown.key }}!', []);

    expect($content->bodyHtml)->toContain('{{ unknown.key }}')
        ->and($content->missingPlaceholders)->toBe(['unknown.key']);
});
