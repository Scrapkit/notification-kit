<?php

declare(strict_types=1);

namespace Scrapkit\NotificationKit\Domain\Templates;

use Illuminate\Contracts\Cache\Factory as CacheFactory;
use Psr\Log\LoggerInterface;
use Scrapkit\NotificationKit\Domain\Templates\DataTransferObjects\EffectiveTemplate;
use Scrapkit\NotificationKit\Domain\Templates\DataTransferObjects\TemplateDefinition;
use Scrapkit\NotificationKit\Domain\Templates\Enums\ContentSource;
use Scrapkit\NotificationKit\Domain\Templates\Models\NotificationTemplate;
use Scrapkit\NotificationKit\Support\KitConfig;
use Throwable;

/**
 * Resolves the content a managed class should use right now.
 *
 * A missing row or an unreachable database must never block transactional
 * mail, so both fall back to the definition shipped in code.
 */
final class TemplateResolver
{
    public function __construct(
        private readonly CacheFactory $cache,
        private readonly LoggerInterface $logger,
    ) {}

    public function resolve(TemplateDefinition $definition): EffectiveTemplate
    {
        $ttl = KitConfig::cacheTtl();

        if ($ttl <= 0) {
            return $this->fresh($definition);
        }

        /** @var ?array<string, mixed> $cached */
        $cached = $this->cache->store(KitConfig::cacheStore())->get($this->cacheKey($definition->key));

        if (is_array($cached)) {
            return $this->hydrate($definition, $cached);
        }

        $effective = $this->fresh($definition);

        if ($effective->source === ContentSource::Database) {
            $this->cache->store(KitConfig::cacheStore())->put(
                $this->cacheKey($definition->key),
                $this->dehydrate($effective),
                $ttl,
            );
        }

        return $effective;
    }

    public function forget(string $key): void
    {
        $this->cache->store(KitConfig::cacheStore())->forget($this->cacheKey($key));
    }

    private function fresh(TemplateDefinition $definition): EffectiveTemplate
    {
        try {
            $template = NotificationTemplate::query()->where('key', $definition->key)->first();
        } catch (Throwable $exception) {
            $this->logger->error('notification-kit: template lookup failed, using code defaults', [
                'template' => $definition->key,
                'exception' => $exception->getMessage(),
            ]);

            return $this->fromDefinition($definition);
        }

        if ($template === null) {
            $this->logger->notice('notification-kit: template not synced, using code defaults', [
                'template' => $definition->key,
            ]);

            return $this->fromDefinition($definition);
        }

        if ($template->isArchived()) {
            $this->logger->warning('notification-kit: sending an archived template', [
                'template' => $definition->key,
            ]);
        }

        return new EffectiveTemplate(
            key: $template->key,
            subject: $template->effectiveSubject(),
            body: $template->effectiveBody(),
            requiresConfirmation: $template->requires_confirmation,
            metadata: $template->metadata ?? [],
            source: ContentSource::Database,
            archived: $template->isArchived(),
        );
    }

    private function fromDefinition(TemplateDefinition $definition): EffectiveTemplate
    {
        return new EffectiveTemplate(
            key: $definition->key,
            subject: $definition->defaultSubject,
            body: $definition->defaultBody,
            requiresConfirmation: $definition->requiresConfirmation,
            metadata: $definition->metadata,
            source: ContentSource::CodeDefault,
            archived: false,
        );
    }

    /**
     * @return array<string, mixed>
     */
    private function dehydrate(EffectiveTemplate $template): array
    {
        return [
            'subject' => $template->subject,
            'body' => $template->body,
            'requires_confirmation' => $template->requiresConfirmation,
            'metadata' => $template->metadata,
            'archived' => $template->archived,
        ];
    }

    /**
     * @param  array<string, mixed>  $cached
     */
    private function hydrate(TemplateDefinition $definition, array $cached): EffectiveTemplate
    {
        $subject = $cached['subject'] ?? null;
        $body = $cached['body'] ?? null;
        $metadata = $cached['metadata'] ?? [];

        return new EffectiveTemplate(
            key: $definition->key,
            subject: is_string($subject) ? $subject : null,
            body: is_string($body) ? $body : $definition->defaultBody,
            requiresConfirmation: (bool) ($cached['requires_confirmation'] ?? false),
            metadata: is_array($metadata) ? $metadata : [],
            source: ContentSource::Database,
            archived: (bool) ($cached['archived'] ?? false),
        );
    }

    private function cacheKey(string $key): string
    {
        return "notification-kit.template.{$key}";
    }
}
