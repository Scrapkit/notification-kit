<?php

declare(strict_types=1);

namespace Scrapkit\NotificationKit\Domain\Templates\Actions;

use Scrapkit\NotificationKit\Contracts\Manageable;
use Scrapkit\NotificationKit\Domain\Templates\DataTransferObjects\SyncResult;
use Scrapkit\NotificationKit\Domain\Templates\DataTransferObjects\TemplateDefinition;
use Scrapkit\NotificationKit\Domain\Templates\Models\NotificationTemplate;
use Scrapkit\NotificationKit\Domain\Templates\TemplateResolver;
use Scrapkit\NotificationKit\Exceptions\NotificationKitException;

/**
 * Upserts the template definitions declared in code.
 *
 * Only code-owned columns are refreshed: the subject, body, metadata,
 * confirmation flag and archived state belong to whoever edits them in the UI
 * and are never overwritten by a sync.
 */
final class SyncTemplatesAction
{
    public function __construct(private readonly TemplateResolver $resolver) {}

    /**
     * @param  list<class-string>  $classes
     */
    public function execute(array $classes): SyncResult
    {
        $created = [];
        $updated = [];
        $keys = [];

        foreach ($classes as $class) {
            $definition = $this->definitionFor($class);
            $keys[] = $definition->key;

            $template = NotificationTemplate::query()->where('key', $definition->key)->first();

            if ($template === null) {
                $this->create($definition);
                $created[] = $definition->key;

                continue;
            }

            $this->refresh($template, $definition);
            $updated[] = $definition->key;

            $this->resolver->forget($definition->key);
        }

        $orphaned = NotificationTemplate::query()
            ->whereNotIn('key', $keys)
            ->orderBy('key')
            ->pluck('key')
            ->all();

        /** @var list<string> $orphaned */
        return new SyncResult($created, $updated, $orphaned);
    }

    /**
     * @param  class-string  $class
     */
    private function definitionFor(string $class): TemplateDefinition
    {
        if (! is_subclass_of($class, Manageable::class)) {
            throw new NotificationKitException(
                "[{$class}] is registered in notification-kit.manageables but does not implement ".Manageable::class.'.'
            );
        }

        return $class::template();
    }

    private function create(TemplateDefinition $definition): void
    {
        NotificationTemplate::query()->create([
            'key' => $definition->key,
            'type' => $definition->type,
            'name' => $definition->name,
            'description' => $definition->description,
            'subject' => null,
            'body' => null,
            'default_subject' => $definition->defaultSubject,
            'default_body' => $definition->defaultBody,
            'placeholders' => $definition->placeholdersToArray(),
            'sample_data' => $definition->sampleData,
            'metadata' => $definition->metadata,
            'requires_confirmation' => $definition->requiresConfirmation,
            'synced_at' => now(),
        ]);
    }

    private function refresh(NotificationTemplate $template, TemplateDefinition $definition): void
    {
        $template->update([
            'type' => $definition->type,
            'name' => $definition->name,
            'description' => $definition->description,
            'default_subject' => $definition->defaultSubject,
            'default_body' => $definition->defaultBody,
            'placeholders' => $definition->placeholdersToArray(),
            'sample_data' => $definition->sampleData,
            'synced_at' => now(),
        ]);
    }
}
