<?php

declare(strict_types=1);

namespace Scrapkit\NotificationKit\Domain\Rendering;

use Scrapkit\NotificationKit\Support\KitConfig;
use Stringable;

final class PlaceholderResolver
{
    private const string PATTERN = '/\{\{\s*([A-Za-z0-9_.]+)\s*\}\}/';

    /**
     * Resolves the placeholders sitting in a markdown link destination.
     *
     * These have to be substituted before the markdown is parsed: a raw
     * `[text]({{ action.url }})` is not a valid link, so CommonMark would
     * leave the whole thing as plain text. Values are percent-encoded where
     * they would otherwise break the link syntax; unsafe schemes are still
     * caught by the parser's allow_unsafe_links setting.
     *
     * @param  array<string, mixed>  $data
     * @return array{text: string, missing: list<string>}
     */
    public function resolveLinkDestinations(string $markdown, array $data): array
    {
        $missing = [];

        $resolved = (string) preg_replace_callback(
            '/\]\(([^)\n]*)\)/',
            function (array $matches) use ($data, &$missing): string {
                $destination = $this->resolve($matches[1], $data, escapeHtml: false);
                $missing = [...$missing, ...$destination['missing']];

                return ']('.strtr($destination['text'], [
                    ' ' => '%20',
                    '(' => '%28',
                    ')' => '%29',
                ]).')';
            },
            $markdown,
        );

        return ['text' => $resolved, 'missing' => $missing];
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array{text: string, missing: list<string>}
     */
    public function resolve(string $text, array $data, bool $escapeHtml): array
    {
        $missing = [];

        $resolved = (string) preg_replace_callback(
            self::PATTERN,
            function (array $matches) use ($data, $escapeHtml, &$missing): string {
                $key = $matches[1];
                $value = data_get($data, $key);

                if ($value === null || (! is_scalar($value) && ! $value instanceof Stringable)) {
                    $missing[] = $key;

                    return KitConfig::missingPlaceholderPolicy() === 'keep' ? $matches[0] : '';
                }

                $string = (string) $value;

                return $escapeHtml ? e($string) : $string;
            },
            $text,
        );

        return ['text' => $resolved, 'missing' => $missing];
    }
}
