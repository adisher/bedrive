<?php

namespace Common\Core\Middleware;

use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Laravel\Sanctum\Http\Middleware\EnsureFrontendRequestsAreStateful as LaravelMiddleware;

class EnsureFrontendRequestsAreStateful extends LaravelMiddleware
{
    public static function fromFrontend($request): bool
    {
        $domain =
            $request->headers->get('referer') ?:
            $request->headers->get('origin');

        if (is_null($domain)) {
            return false;
        }

        $domain = parse_url($domain, PHP_URL_HOST);
        $domain = Str::replaceFirst('www.', '', $domain);
        $domain = Str::endsWith($domain, '/') ? $domain : "{$domain}/";

        $stateful = [
            ...array_filter(config('sanctum.stateful', [])),
            parse_url(config('app.url'), PHP_URL_HOST)
        ];

        // make sure api calls from api docs page are not considered stateful to avoid 419 errors on POST requests
        if (Str::contains($domain, '/api-docs/')) {
            return false;
        }

        return Str::is(
            Collection::make($stateful)
                ->map(
                    fn($uri) => Str::replaceFirst('www.', '', trim($uri)) .
                        '/*',
                )
                ->all(),
            $domain,
        );
    }
}
