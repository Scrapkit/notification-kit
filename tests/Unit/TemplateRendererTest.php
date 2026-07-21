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

it('resolves placeholders used as a link destination', function (): void {
    $content = app(TemplateRenderer::class)->render(
        null,
        '[Imposta la password]({{ action.url }})',
        ['action' => ['url' => 'https://app.test/reset/abc123']],
    );

    expect($content->bodyHtml)->toContain('<a href="https://app.test/reset/abc123">Imposta la password</a>')
        ->and($content->missingPlaceholders)->toBe([]);
});

it('keeps a link usable when the resolved url contains spaces or parentheses', function (): void {
    $content = app(TemplateRenderer::class)->render(
        null,
        '[Apri]({{ action.url }})',
        ['action' => ['url' => 'https://app.test/a b(c)']],
    );

    expect($content->bodyHtml)->toContain('href="https://app.test/a%20b%28c%29"')
        ->and($content->bodyHtml)->toContain('>Apri</a>');
});

it('drops a link whose resolved url uses an unsafe scheme', function (): void {
    $content = app(TemplateRenderer::class)->render(
        null,
        '[Clicca]({{ action.url }})',
        ['action' => ['url' => 'javascript:alert(1)']],
    );

    expect($content->bodyHtml)->not->toContain('javascript:alert');
});

it('reports a missing placeholder used as a link destination', function (): void {
    $content = app(TemplateRenderer::class)->render(null, '[Apri]({{ action.url }})', []);

    expect($content->missingPlaceholders)->toBe(['action.url']);
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
