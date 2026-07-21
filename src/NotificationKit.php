<?php

declare(strict_types=1);

namespace Scrapkit\NotificationKit;

use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Support\Facades\Auth;
use Scrapkit\NotificationKit\Domain\Outbox\Actions\ApproveOutboxMessageAction;
use Scrapkit\NotificationKit\Domain\Outbox\Actions\CancelOutboxMessageAction;
use Scrapkit\NotificationKit\Domain\Outbox\Actions\SendManagedMailAction;
use Scrapkit\NotificationKit\Domain\Outbox\DataTransferObjects\RecipientsSnapshot;
use Scrapkit\NotificationKit\Domain\Outbox\DataTransferObjects\SendDispatch;
use Scrapkit\NotificationKit\Domain\Outbox\Models\OutboxMessage;
use Scrapkit\NotificationKit\Mail\ManagedMailable;

/**
 * The entry point host applications send managed mail through.
 */
final class NotificationKit
{
    public function __construct(
        private readonly SendManagedMailAction $sendAction,
        private readonly ApproveOutboxMessageAction $approveAction,
        private readonly CancelOutboxMessageAction $cancelAction,
    ) {}

    public function to(mixed $recipients): PendingManagedMail
    {
        return (new PendingManagedMail($this))->to($recipients);
    }

    public function send(ManagedMailable $mailable, RecipientsSnapshot $recipients): SendDispatch
    {
        return $this->sendAction->execute($mailable, $recipients, Auth::user());
    }

    public function approve(OutboxMessage $message, ?Authenticatable $decidedBy = null): OutboxMessage
    {
        return $this->approveAction->execute($message, $decidedBy ?? Auth::user());
    }

    public function cancel(OutboxMessage $message, ?Authenticatable $decidedBy = null): OutboxMessage
    {
        return $this->cancelAction->execute($message, $decidedBy ?? Auth::user());
    }
}
