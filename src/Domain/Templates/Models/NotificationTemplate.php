<?php

declare(strict_types=1);

namespace Scrapkit\NotificationKit\Domain\Templates\Models;

use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Query\Builder;
use Scrapkit\NotificationKit\Database\Factories\NotificationTemplateFactory;
use Scrapkit\NotificationKit\Domain\Templates\Enums\TemplateType;
use Scrapkit\NotificationKit\Domain\Templates\QueryBuilders\NotificationTemplateBuilder;
use Scrapkit\NotificationKit\Support\KitConfig;

/**
 * @property int $id
 * @property string $key
 * @property TemplateType $type
 * @property string $name
 * @property ?string $description
 * @property ?string $subject
 * @property ?string $body
 * @property ?string $default_subject
 * @property string $default_body
 * @property array<int, array{key: string, description: string, example: ?string}> $placeholders
 * @property array<string, mixed> $sample_data
 * @property ?array<string, mixed> $metadata
 * @property bool $requires_confirmation
 * @property bool $supports_confirmation
 * @property ?CarbonInterface $archived_at
 * @property ?CarbonInterface $synced_at
 * @property ?CarbonInterface $created_at
 * @property ?CarbonInterface $updated_at
 */
final class NotificationTemplate extends Model
{
    /** @use HasFactory<NotificationTemplateFactory> */
    use HasFactory;

    protected $fillable = [
        'key',
        'type',
        'name',
        'description',
        'subject',
        'body',
        'default_subject',
        'default_body',
        'placeholders',
        'sample_data',
        'metadata',
        'requires_confirmation',
        'supports_confirmation',
        'archived_at',
        'synced_at',
    ];

    public function getTable(): string
    {
        return KitConfig::tablePrefix().'templates';
    }

    public function getConnectionName(): ?string
    {
        return KitConfig::connection() ?? parent::getConnectionName();
    }

    public function getRouteKeyName(): string
    {
        return 'key';
    }

    /**
     * @return HasMany<TemplateVersion, $this>
     */
    public function versions(): HasMany
    {
        return $this->hasMany(TemplateVersion::class, 'template_id');
    }

    /**
     * @param  Builder  $query
     */
    public function newEloquentBuilder($query): NotificationTemplateBuilder
    {
        return new NotificationTemplateBuilder($query);
    }

    public function isArchived(): bool
    {
        return $this->archived_at !== null;
    }

    public function isCustomized(): bool
    {
        return $this->subject !== null || $this->body !== null;
    }

    public function effectiveSubject(): ?string
    {
        return $this->subject ?? $this->default_subject;
    }

    public function effectiveBody(): string
    {
        return $this->body ?? $this->default_body;
    }

    protected static function newFactory(): NotificationTemplateFactory
    {
        return NotificationTemplateFactory::new();
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'type' => TemplateType::class,
            'placeholders' => 'array',
            'sample_data' => 'array',
            'metadata' => 'array',
            'requires_confirmation' => 'boolean',
            'supports_confirmation' => 'boolean',
            'archived_at' => 'datetime',
            'synced_at' => 'datetime',
        ];
    }
}
