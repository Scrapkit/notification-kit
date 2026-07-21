<?php

declare(strict_types=1);

namespace Scrapkit\NotificationKit\Domain\Templates\Models;

use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Scrapkit\NotificationKit\Database\Factories\TemplateVersionFactory;
use Scrapkit\NotificationKit\Support\KitConfig;

/**
 * Immutable snapshot of a template's user-owned configuration after a change.
 *
 * @property int $id
 * @property int $template_id
 * @property ?string $subject
 * @property ?string $body
 * @property ?array<string, mixed> $metadata
 * @property bool $requires_confirmation
 * @property ?string $edited_by_type
 * @property ?int $edited_by_id
 * @property ?CarbonInterface $created_at
 */
final class TemplateVersion extends Model
{
    /** @use HasFactory<TemplateVersionFactory> */
    use HasFactory;

    public const null UPDATED_AT = null;

    protected $fillable = [
        'template_id',
        'subject',
        'body',
        'metadata',
        'requires_confirmation',
        'edited_by_type',
        'edited_by_id',
    ];

    public function getTable(): string
    {
        return KitConfig::tablePrefix().'template_versions';
    }

    public function getConnectionName(): ?string
    {
        return KitConfig::connection() ?? parent::getConnectionName();
    }

    /**
     * @return BelongsTo<NotificationTemplate, $this>
     */
    public function template(): BelongsTo
    {
        return $this->belongsTo(NotificationTemplate::class, 'template_id');
    }

    /**
     * @return MorphTo<Model, $this>
     */
    public function editedBy(): MorphTo
    {
        return $this->morphTo('edited_by');
    }

    protected static function newFactory(): TemplateVersionFactory
    {
        return TemplateVersionFactory::new();
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'metadata' => 'array',
            'requires_confirmation' => 'boolean',
        ];
    }
}
