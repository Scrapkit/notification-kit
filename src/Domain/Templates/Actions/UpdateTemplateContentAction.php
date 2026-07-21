<?php

declare(strict_types=1);

namespace Scrapkit\NotificationKit\Domain\Templates\Actions;

use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use Scrapkit\NotificationKit\Domain\Templates\DataTransferObjects\TemplateContentData;
use Scrapkit\NotificationKit\Domain\Templates\Events\TemplateContentUpdated;
use Scrapkit\NotificationKit\Domain\Templates\Models\NotificationTemplate;
use Scrapkit\NotificationKit\Domain\Templates\Models\TemplateVersion;
use Scrapkit\NotificationKit\Domain\Templates\TemplateResolver;

/**
 * Applies an edit and records it in the immutable history.
 */
final class UpdateTemplateContentAction
{
    public function __construct(private readonly TemplateResolver $resolver) {}

    public function execute(
        NotificationTemplate $template,
        TemplateContentData $content,
        ?Authenticatable $editedBy,
    ): NotificationTemplate {
        $version = DB::transaction(function () use ($template, $content, $editedBy): TemplateVersion {
            $template->update([
                'subject' => $content->subject,
                'body' => $content->body,
                'metadata' => $content->metadata,
                'requires_confirmation' => $content->requiresConfirmation,
            ]);

            return TemplateVersion::query()->create([
                'template_id' => $template->id,
                'subject' => $content->subject,
                'body' => $content->body,
                'metadata' => $content->metadata,
                'requires_confirmation' => $content->requiresConfirmation,
                'edited_by_type' => $editedBy instanceof Model ? $editedBy->getMorphClass() : null,
                'edited_by_id' => $editedBy instanceof Model ? $editedBy->getKey() : null,
            ]);
        });

        $this->resolver->forget($template->key);

        Event::dispatch(new TemplateContentUpdated($template, $version));

        return $template;
    }
}
