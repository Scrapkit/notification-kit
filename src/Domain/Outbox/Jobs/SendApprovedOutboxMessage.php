<?php

declare(strict_types=1);

namespace Scrapkit\NotificationKit\Domain\Outbox\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Mail\Mailer;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Mail\PendingMail;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Event;
use Scrapkit\NotificationKit\Domain\Outbox\DataTransferObjects\RecipientsSnapshot;
use Scrapkit\NotificationKit\Domain\Outbox\Enums\OutboxStatus;
use Scrapkit\NotificationKit\Domain\Outbox\Events\MessageFailed;
use Scrapkit\NotificationKit\Domain\Outbox\Events\MessageSent;
use Scrapkit\NotificationKit\Domain\Outbox\Models\OutboxMessage;
use Scrapkit\NotificationKit\Mail\OutboxMail;
use Scrapkit\NotificationKit\Support\KitConfig;
use Throwable;

final class SendApprovedOutboxMessage implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public function __construct(public readonly OutboxMessage $message) {}

    public function tries(): int
    {
        return KitConfig::queueTries();
    }

    public function backoff(): int
    {
        return KitConfig::queueBackoff();
    }

    public function handle(?Mailer $mailer = null): void
    {
        $message = $this->message->fresh() ?? $this->message;

        // A retry can race with a status change; only an approved message sends.
        if ($message->status !== OutboxStatus::Approved) {
            return;
        }

        $mailer ??= app(Mailer::class);

        /** @var PendingMail $pending */
        $pending = RecipientsSnapshot::fromArray($message->recipients)->applyTo($mailer->to([]));
        $pending->send(new OutboxMail($message));

        $message->sent_at = now();
        $message->transitionTo(OutboxStatus::Sent);

        Event::dispatch(new MessageSent($message));
    }

    public function failed(Throwable $exception): void
    {
        $message = $this->message->fresh() ?? $this->message;

        if ($message->status !== OutboxStatus::Approved) {
            return;
        }

        $message->error = $exception->getMessage();
        $message->transitionTo(OutboxStatus::Failed);

        Event::dispatch(new MessageFailed($message, $exception->getMessage()));
    }
}
