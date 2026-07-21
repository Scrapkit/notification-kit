<?php

declare(strict_types=1);

namespace Scrapkit\NotificationKit\Domain\Outbox\DataTransferObjects;

use Illuminate\Contracts\Mail\Mailable;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Mail\PendingMail;

/**
 * The to/cc/bcc addresses a send was requested for.
 */
final readonly class RecipientsSnapshot
{
    /**
     * @param  list<RecipientData>  $recipients
     */
    public function __construct(public array $recipients = []) {}

    public function with(string $type, mixed $recipients): self
    {
        return new self([...$this->recipients, ...self::normalize($type, $recipients)]);
    }

    public function isEmpty(): bool
    {
        return $this->recipients === [];
    }

    /**
     * @return list<array{type: string, address: string, name: ?string}>
     */
    public function toArray(): array
    {
        return array_map(static fn (RecipientData $r): array => $r->toArray(), $this->recipients);
    }

    /**
     * @param  array<int, array{type: string, address: string, name: ?string}>  $recipients
     */
    public static function fromArray(array $recipients): self
    {
        return new self(array_values(array_map(
            static fn (array $r): RecipientData => new RecipientData($r['type'], $r['address'], $r['name'] ?? null),
            $recipients,
        )));
    }

    /**
     * Applies the snapshot to a pending mail builder.
     */
    public function applyTo(PendingMail $pending): PendingMail
    {
        foreach (['to', 'cc', 'bcc'] as $type) {
            $addresses = array_values(array_filter(
                $this->recipients,
                static fn (RecipientData $r): bool => $r->type === $type,
            ));

            if ($addresses !== []) {
                $pending = $pending->{$type}(array_map(
                    static fn (RecipientData $r): array => ['email' => $r->address, 'name' => $r->name],
                    $addresses,
                ));
            }
        }

        return $pending;
    }

    /**
     * @return list<RecipientData>
     */
    private static function normalize(string $type, mixed $recipients): array
    {
        if ($recipients instanceof Mailable) {
            return [];
        }

        if (is_string($recipients)) {
            return [new RecipientData($type, $recipients)];
        }

        if ($recipients instanceof Arrayable) {
            $recipients = $recipients->toArray();
        }

        if (is_object($recipients)) {
            return self::fromObject($type, $recipients);
        }

        if (! is_array($recipients)) {
            return [];
        }

        if (isset($recipients['email']) && is_string($recipients['email'])) {
            $name = $recipients['name'] ?? null;

            return [new RecipientData($type, $recipients['email'], is_string($name) ? $name : null)];
        }

        $normalized = [];

        foreach ($recipients as $recipient) {
            foreach (self::normalize($type, $recipient) as $entry) {
                $normalized[] = $entry;
            }
        }

        return $normalized;
    }

    /**
     * @return list<RecipientData>
     */
    private static function fromObject(string $type, object $recipient): array
    {
        $email = $recipient->email ?? null;

        if (! is_string($email)) {
            return [];
        }

        $name = $recipient->name ?? null;

        return [new RecipientData($type, $email, is_string($name) ? $name : null)];
    }
}
