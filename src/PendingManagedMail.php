<?php

declare(strict_types=1);

namespace Scrapkit\NotificationKit;

use Scrapkit\NotificationKit\Domain\Outbox\DataTransferObjects\RecipientsSnapshot;
use Scrapkit\NotificationKit\Domain\Outbox\DataTransferObjects\SendDispatch;
use Scrapkit\NotificationKit\Mail\ManagedMailable;

final class PendingManagedMail
{
    private RecipientsSnapshot $recipients;

    public function __construct(private readonly NotificationKit $kit)
    {
        $this->recipients = new RecipientsSnapshot;
    }

    public function to(mixed $recipients): self
    {
        $this->recipients = $this->recipients->with('to', $recipients);

        return $this;
    }

    public function cc(mixed $recipients): self
    {
        $this->recipients = $this->recipients->with('cc', $recipients);

        return $this;
    }

    public function bcc(mixed $recipients): self
    {
        $this->recipients = $this->recipients->with('bcc', $recipients);

        return $this;
    }

    public function send(ManagedMailable $mailable): SendDispatch
    {
        return $this->kit->send($mailable, $this->recipients);
    }
}
