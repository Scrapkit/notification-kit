<?php

declare(strict_types=1);

namespace Scrapkit\NotificationKit\Domain\Outbox\Actions;

use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Contracts\Mail\Mailer;
use Illuminate\Mail\PendingMail;
use Scrapkit\NotificationKit\Domain\Outbox\DataTransferObjects\RecipientsSnapshot;
use Scrapkit\NotificationKit\Domain\Outbox\DataTransferObjects\SendDispatch;
use Scrapkit\NotificationKit\Domain\Outbox\Enums\SendOutcome;
use Scrapkit\NotificationKit\Domain\Templates\TemplateResolver;
use Scrapkit\NotificationKit\Mail\ManagedMailable;

/**
 * The fork in the pipeline: send now, or hold for manual confirmation.
 */
final class SendManagedMailAction
{
    public function __construct(
        private readonly TemplateResolver $resolver,
        private readonly RequestConfirmableSendAction $requestConfirmation,
        private readonly Mailer $mailer,
    ) {}

    public function execute(
        ManagedMailable $mailable,
        RecipientsSnapshot $recipients,
        ?Authenticatable $requestedBy,
    ): SendDispatch {
        $effective = $this->resolver->resolve($mailable::template());

        if ($effective->requiresConfirmation) {
            return new SendDispatch(
                SendOutcome::PendingConfirmation,
                $this->requestConfirmation->execute($mailable, $effective, $recipients, $requestedBy),
            );
        }

        $mailable->confirmationHandled();

        /** @var PendingMail $pending */
        $pending = $recipients->applyTo($this->mailer->to([]));
        $pending->send($mailable);

        return new SendDispatch(SendOutcome::SentDirectly);
    }
}
