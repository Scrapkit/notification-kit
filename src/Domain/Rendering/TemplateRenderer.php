<?php

declare(strict_types=1);

namespace Scrapkit\NotificationKit\Domain\Rendering;

use Illuminate\Support\Str;
use Psr\Log\LoggerInterface;

/**
 * Renders template markdown into HTML with safely substituted placeholders.
 *
 * Content coming from the database is never compiled as Blade: markdown is
 * rendered with html_input=escape and unsafe links disabled, and placeholder
 * values are substituted into the rendered HTML already escaped.
 */
final class TemplateRenderer
{
    public function __construct(
        private readonly PlaceholderResolver $placeholders,
        private readonly LoggerInterface $logger,
    ) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public function render(?string $subject, string $bodyMarkdown, array $data): RenderedContent
    {
        $bodyHtml = (string) Str::markdown($bodyMarkdown, [
            'html_input' => 'escape',
            'allow_unsafe_links' => false,
        ]);

        $body = $this->placeholders->resolve($bodyHtml, $data, escapeHtml: true);
        $missing = $body['missing'];

        $resolvedSubject = null;

        if ($subject !== null) {
            $subjectResult = $this->placeholders->resolve($subject, $data, escapeHtml: false);
            $resolvedSubject = $subjectResult['text'];
            $missing = array_values(array_unique([...$missing, ...$subjectResult['missing']]));
        }

        if ($missing !== []) {
            $this->logger->warning('notification-kit: missing placeholders while rendering', [
                'missing' => $missing,
            ]);
        }

        return new RenderedContent($resolvedSubject, trim($body['text']), $missing);
    }
}
