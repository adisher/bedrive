<?php

namespace Common\Core\Middleware;

use Closure;
use Common\Settings\Settings;
use Illuminate\Auth\Middleware\EnsureEmailIsVerified as LaravelMiddleware;

class EnsureEmailIsVerified extends LaravelMiddleware
{
    public function handle($request, Closure $next, $redirectToRoute = null)
    {
        // bail if user is not logged in, it will be handled by policies
        // also bail if email verification is disabled from settings page
        if (
            !$request->user() ||
            !app(Settings::class)->get('require_email_confirmation')
        ) {
            return $next($request);
        }

        return parent::handle($request, $next, $redirectToRoute);
    }
}
