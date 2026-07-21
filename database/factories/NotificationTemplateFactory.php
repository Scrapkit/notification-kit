<?php

declare(strict_types=1);

namespace Scrapkit\NotificationKit\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Scrapkit\NotificationKit\Domain\Templates\Enums\TemplateType;
use Scrapkit\NotificationKit\Domain\Templates\Models\NotificationTemplate;

/**
 * @extends Factory<NotificationTemplate>
 */
final class NotificationTemplateFactory extends Factory
{
    protected $model = NotificationTemplate::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'key' => 'mail.'.fake()->unique()->slug(3),
            'type' => TemplateType::Email,
            'name' => fake()->words(3, true),
            'description' => fake()->sentence(),
            'subject' => null,
            'body' => null,
            'default_subject' => fake()->sentence(4),
            'default_body' => "Hi {{ user.name }},\n\n".fake()->paragraph(),
            'placeholders' => [
                ['key' => 'user.name', 'description' => 'Recipient full name', 'example' => 'Ada Lovelace'],
            ],
            'sample_data' => ['user' => ['name' => 'Ada Lovelace']],
            'metadata' => [],
            'requires_confirmation' => false,
            'archived_at' => null,
            'synced_at' => null,
        ];
    }

    public function email(): static
    {
        return $this->state(['type' => TemplateType::Email]);
    }

    public function notification(): static
    {
        return $this->state(['type' => TemplateType::Notification]);
    }

    public function archived(): static
    {
        return $this->state(['archived_at' => now()]);
    }

    public function confirmable(): static
    {
        return $this->state(['requires_confirmation' => true]);
    }

    public function customized(): static
    {
        return $this->state([
            'subject' => fake()->sentence(4),
            'body' => "Ciao {{ user.name }},\n\n".fake()->paragraph(),
        ]);
    }
}
