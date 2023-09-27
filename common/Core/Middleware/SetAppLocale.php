<?php

namespace Common\Core\Middleware;

use Closure;
use Common\Localizations\Localization;
use Common\Localizations\UserLocaleController;
use Common\Settings\Settings;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cookie;
use Negotiation\LanguageNegotiator;

class SetAppLocale
{
    public function handle(Request $request, Closure $next)
    {
        if (app(Settings::class)->get('i18n.enable')) {
            // 1. Check if current user has manually selected a specific language
            $langCode =
                $request->get('lang') ??
                ($request->user()->language ??
                    Cookie::get(UserLocaleController::COOKIE_NAME));

            $defaultLocale = app(Settings::class)->get(
                'locale.default',
                'auto',
            );

            // 2. if admin manually selected a specific default locale, use that
            if (!$langCode && $defaultLocale && $defaultLocale !== 'auto') {
                $langCode = $defaultLocale;
            }

            // 3. Try to use language based on browser settings
            if (!$langCode && ($header = $request->header('Accept-Language'))) {
                $bestLanguage = (new LanguageNegotiator())->getBest(
                    $header,
                    Localization::pluck('language')->toArray(),
                );
                $langCode = $bestLanguage?->getBasePart();
            }

            if ($langCode) {
                app()->setLocale($langCode);
            }
        }

        return $next($request);
    }
}
