<?php

namespace Common\Core\Bootstrap;

use App\User;
use Common\Localizations\Localization;
use Illuminate\Support\Str;
use Spatie\Color\Hex;
use Spatie\Color\Rgb;

class MobileBootstrapData extends BaseBootstrapData
{
    public function init(): self
    {
        $cssThemes = $this->getThemes()['all'];
        $themes = [
            'light' => $cssThemes
                ->where('default_light', true)
                ->first()
                ->toArray(),
            'dark' => $cssThemes
                ->where('default_dark', true)
                ->first()
                ->toArray(),
        ];

        $themes['light']['colors'] = $this->mapColorsToRgba(
            $themes['light']['colors'],
        );
        $themes['dark']['colors'] = $this->mapColorsToRgba(
            $themes['dark']['colors'],
        );

        $this->data = [
            'themes' => $themes,
            'user' => $this->getCurrentUser(),
            'menus' => $this->getMobileMenus(),
            'settings' => [
                'social.google.enable' => (bool) $this->settings->get(
                    'social.google.enable',
                ),
            ],
            'locales' => Localization::get(),
        ];

        $this->logActiveSession();

        return $this;
    }

    public function refreshToken(string $deviceName): self
    {
        /* @var User $user */
        $user = $this->data['user'];
        if ($user) {
            $user['access_token'] = $user->refreshApiToken($deviceName);
            $this->loadFcmToken($user);
        }
        return $this;
    }

    public function getCurrentUser(): ?User
    {
        /* @var User $user */
        if ($user = $this->request->user()) {
            return $this->loadFcmToken($user);
        }
        return null;
    }

    private function getMobileMenus(): array
    {
        return array_values(
            array_filter(
                $this->settings->getJson('menus'),
                fn($menu) => collect($menu['positions'])->some(
                    fn($position) => Str::startsWith($position, 'mobile-app'),
                ),
            ),
        );
    }

    private function mapColorsToRgba(array $colors): array
    {
        if (!class_exists(Hex::class)) {
            return $colors;
        }

        return array_map(function ($color) {
            if (str_ends_with($color, '%')) {
                return (int) str_replace('%', '', $color);
            } else {
                $color = str_replace(' ', ',', $color);
                $rgb = Rgb::fromString("rgb($color)");
                return [$rgb->red(), $rgb->green(), $rgb->blue(), 1.0];
            }
        }, $colors);
    }

    private function loadFcmToken(User $user): User
    {
        if (method_exists($user, 'loadFcmToken')) {
            $user->loadFcmToken();
        }
        return $user;
    }
}
