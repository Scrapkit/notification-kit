<?php

declare(strict_types=1);

namespace Scrapkit\NotificationKit\Domain\Outbox\Exceptions;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Scrapkit\NotificationKit\Domain\Outbox\Enums\OutboxStatus;
use Scrapkit\NotificationKit\Exceptions\NotificationKitException;
use Symfony\Component\HttpFoundation\Response;

final class InvalidOutboxTransition extends NotificationKitException
{
    public static function make(OutboxStatus $from, OutboxStatus $to): self
    {
        return new self("Cannot transition an outbox message from [{$from->value}] to [{$to->value}].");
    }

    /**
     * A decision on a message somebody else already settled is a conflict,
     * not a server error.
     */
    public function render(Request $request): ?JsonResponse
    {
        if (! $request->expectsJson()) {
            return null;
        }

        return new JsonResponse(['message' => $this->getMessage()], Response::HTTP_CONFLICT);
    }
}
