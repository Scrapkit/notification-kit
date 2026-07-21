<?php

declare(strict_types=1);

namespace Scrapkit\NotificationKit\Authorization;

enum Ability: string
{
    case View = 'view';
    case UpdateContent = 'update-content';
    case Archive = 'archive';
    case Approve = 'approve';

    public function gateName(): string
    {
        return 'notification-kit.'.$this->value;
    }
}
