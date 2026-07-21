<?php

declare(strict_types=1);

namespace Scrapkit\NotificationKit\Domain\Rendering;

use Scrapkit\NotificationKit\Support\KitConfig;
use Stringable;

final class PlaceholderResolver
{
    private const string PATTERN = '/\{\{\s*([A-Za-z0-9_.]+)\s*\}\}/';

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
