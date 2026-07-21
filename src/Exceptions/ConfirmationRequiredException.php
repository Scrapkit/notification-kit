<?php

declare(strict_types=1);

namespace Scrapkit\NotificationKit\Exceptions;

final class ConfirmationRequiredException extends NotificationKitException
{
    public static function for(string $mailable, string $templateKey): self
    {
        return new self(
            "[{$mailable}] uses template [{$templateKey}], which requires manual confirmation. ".
            'Send it through NotificationKit::to(...)->send(...) so it is queued for approval.'
        );
    }
}
