<?php

declare(strict_types=1);

namespace Scrapkit\NotificationKit\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Scrapkit\NotificationKit\Domain\Templates\Models\TemplateVersion;

/**
 * @extends Factory<TemplateVersion>
 */
final class TemplateVersionFactory extends Factory
{
    protected $model = TemplateVersion::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'template_id' => NotificationTemplateFactory::new(),
            'subject' => fake()->sentence(4),
            'body' => fake()->paragraph(),
            'metadata' => [],
            'requires_confirmation' => false,
            'edited_by_type' => null,
            'edited_by_id' => null,
        ];
    }
}
