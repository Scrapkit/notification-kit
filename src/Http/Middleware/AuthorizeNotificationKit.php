<?php

declare(strict_types=1);

namespace Scrapkit\NotificationKit\Http\Middleware;

use Closure;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Http\Request;
use Scrapkit\NotificationKit\Authorization\NotificationKitGate;
use Symfony\Component\HttpFoundation\Response;

final class AuthorizeNotificationKit
{
    /**
     * @param  Closure(Request): Response  $next
     *
     * @throws AuthenticationException|AuthorizationException
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user === null) {
            throw new AuthenticationException;
        }

        if (! NotificationKitGate::allowsEntry($user)) {
            throw new AuthorizationException;
        }

        return $next($request);
    }
}
