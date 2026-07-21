<?php

declare(strict_types=1);

namespace Scrapkit\NotificationKit\Domain\Outbox\Models;

use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Str;
use Scrapkit\NotificationKit\Database\Factories\OutboxMessageFactory;
use Scrapkit\NotificationKit\Domain\Outbox\Enums\OutboxStatus;
use Scrapkit\NotificationKit\Domain\Outbox\Exceptions\InvalidOutboxTransition;
use Scrapkit\NotificationKit\Domain\Outbox\QueryBuilders\OutboxMessageBuilder;
use Scrapkit\NotificationKit\Domain\Outbox\States\OutboxState;
use Scrapkit\NotificationKit\Domain\Templates\Models\NotificationTemplate;
use Scrapkit\NotificationKit\Support\KitConfig;

/**
 * A confirmable email held for manual approval, with its rendered snapshot.
 *
 * @property int $id
 * @property string $uuid
 * @property ?int $template_id
 * @property string $template_key
 * @property string $mailable_class
 * @property string $rendered_subject
 * @property string $rendered_body_html
 * @property array<int, array{type: string, address: string, name: ?string}> $recipients
 * @property ?array<string, mixed> $envelope
 * @property OutboxStatus $status
 * @property ?string $requested_by_type
 * @property ?int $requested_by_id
 * @property ?string $decided_by_type
 * @property ?int $decided_by_id
 * @property ?CarbonInterface $decided_at
 * @property ?CarbonInterface $sent_at
 * @property ?string $error
 * @property ?CarbonInterface $created_at
 * @property ?CarbonInterface $updated_at
 */
final class OutboxMessage extends Model
{
    /** @use HasFactory<OutboxMessageFactory> */
    use HasFactory;

    protected $fillable = [
        'uuid',
        'template_id',
        'template_key',
        'mailable_class',
        'rendered_subject',
        'rendered_body_html',
        'recipients',
        'envelope',
        'status',
        'requested_by_type',
        'requested_by_id',
        'decided_by_type',
        'decided_by_id',
        'decided_at',
        'sent_at',
        'error',
    ];

    public function getTable(): string
    {
        return KitConfig::tablePrefix().'outbox_messages';
    }

    public function getConnectionName(): ?string
    {
        return KitConfig::connection() ?? parent::getConnectionName();
    }

    public function getRouteKeyName(): string
    {
        return 'uuid';
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
    public function requestedBy(): MorphTo
    {
        return $this->morphTo('requestedBy', 'requested_by_type', 'requested_by_id');
    }

    /**
     * @return MorphTo<Model, $this>
     */
    public function decidedBy(): MorphTo
    {
        return $this->morphTo('decidedBy', 'decided_by_type', 'decided_by_id');
    }

    /**
     * @param  Builder  $query
     */
    public function newEloquentBuilder($query): OutboxMessageBuilder
    {
        return new OutboxMessageBuilder($query);
    }

    public function state(): OutboxState
    {
        return OutboxState::for($this->status);
    }

    /**
     * The single choke point for every status change.
     *
     * @throws InvalidOutboxTransition
     */
    public function transitionTo(OutboxStatus $target): void
    {
        if (! $this->state()->canTransitionTo($target)) {
            throw InvalidOutboxTransition::make($this->status, $target);
        }

        $this->status = $target;
        $this->save();
    }

    protected static function booted(): void
    {
        self::creating(function (self $message): void {
            if ($message->uuid === null) {
                $message->uuid = (string) Str::uuid();
            }
        });
    }

    protected static function newFactory(): OutboxMessageFactory
    {
        return OutboxMessageFactory::new();
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'recipients' => 'array',
            'envelope' => 'array',
            'status' => OutboxStatus::class,
            'decided_at' => 'datetime',
            'sent_at' => 'datetime',
        ];
    }
}
