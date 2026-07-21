<?php

declare(strict_types=1);

namespace Scrapkit\NotificationKit\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;
use Scrapkit\NotificationKit\Domain\Outbox\Enums\OutboxStatus;
use Scrapkit\NotificationKit\Domain\Outbox\Models\OutboxMessage;

/**
 * @extends Factory<OutboxMessage>
 */
final class OutboxMessageFactory extends Factory
{
    protected $model = OutboxMessage::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'uuid' => (string) Str::uuid(),
            'template_id' => NotificationTemplateFactory::new()->confirmable(),
            'template_key' => 'mail.'.fake()->unique()->slug(3),
            'mailable_class' => 'App\\Mail\\ExampleMail',
            'rendered_subject' => fake()->sentence(4),
            'rendered_body_html' => '<p>'.fake()->paragraph().'</p>',
            'recipients' => [
                ['type' => 'to', 'address' => fake()->unique()->safeEmail(), 'name' => fake()->name()],
            ],
            'envelope' => null,
            'status' => OutboxStatus::Pending,
            'requested_by_type' => null,
            'requested_by_id' => null,
            'decided_by_type' => null,
            'decided_by_id' => null,
            'decided_at' => null,
            'sent_at' => null,
            'error' => null,
        ];
    }

    public function approved(): static
    {
        return $this->state([
            'status' => OutboxStatus::Approved,
            'decided_at' => now(),
        ]);
    }

    public function cancelled(): static
    {
        return $this->state([
            'status' => OutboxStatus::Cancelled,
            'decided_at' => now(),
        ]);
    }

    public function sent(): static
    {
        return $this->state([
            'status' => OutboxStatus::Sent,
            'decided_at' => now(),
            'sent_at' => now(),
        ]);
    }

    public function failed(): static
    {
        return $this->state([
            'status' => OutboxStatus::Failed,
            'decided_at' => now(),
            'error' => 'Connection to mail server failed.',
        ]);
    }
}
