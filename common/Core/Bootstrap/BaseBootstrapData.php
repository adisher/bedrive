<?php namespace Common\Core\Bootstrap;

use App\User;
use Common\Admin\Appearance\Themes\CssTheme;
use Common\Auth\Jobs\LogActiveSessionJob;
use Common\Auth\Roles\Role;
use Common\Billing\Gateways\Stripe\FormatsMoney;
use Common\Core\AppUrl;
use Common\Core\Prerender\MetaTags;
use Common\Localizations\LocalizationsRepository;
use Common\Settings\Settings;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;

class BaseBootstrapData implements BootstrapData
{
    use FormatsMoney;

    protected array $data = [];

    public function __construct(
        protected Settings $settings,
        protected Request $request,
        protected Role $role,
        protected LocalizationsRepository $localizationsRepository,
    ) {
    }

    public function getEncoded(): string
    {
        if ($this->data['user']) {
            $this->data['user'] = $this->data['user']->toArray();
        }

        return base64_encode(json_encode($this->data));
    }

    public function get($key = null)
    {
        return $key ? Arr::get($this->data, $key) : $this->data;
    }

    public function getSelectedTheme($key = null)
    {
        $themeId = $this->get('themes.selectedThemeId');
        $theme = Arr::first(
            $this->data['themes']['all'],
            fn($theme) => $theme['id'] === (int) $themeId,
        );
        if (!$theme) {
            $theme = $this->data['themes']['all'][0];
        }

        $value = $key ? Arr::get($theme, $key) : $theme;
        return $key === 'name' ? strtolower($value) : $value;
    }

    public function init(): self
    {
        $this->data['settings'] = $this->settings->getUnflattened();
        $this->data['csrf_token'] = csrf_token();
        $this->data['settings']['base_url'] = config('app.url');
        $this->data['settings']['html_base_uri'] = app(
            AppUrl::class,
        )->htmlBaseUri;
        $this->data['settings']['version'] = config('common.site.version');
        $this->data['default_meta_tags'] = $this->getDefaultMetaTags();
        $this->data['user'] = $this->getCurrentUser();
        $this->data['guest_role'] = app('guestRole')->load('permissions');
        $this->data['i18n'] =
            $this->localizationsRepository->getByNameOrCode(
                app()->getLocale(),
                $this->settings->get('i18n.enable', true),
            ) ?:
            null;
        $this->data['themes'] = $this->getThemes();
        $this->data['language'] = $this->data['i18n']
            ? $this->data['i18n']['language']
            : 'en';

        if (
            config('common.site.notifications_integrated') &&
            $this->data['user']
        ) {
            $this->data['user']->loadCount('unreadNotifications');
        }

        $alreadyAccepted =
            !$this->settings->get('cookie_notice.enable') ||
            (bool) Arr::get($_COOKIE, 'cookie_notice', false);
        $this->data['show_cookie_notice'] =
            !$alreadyAccepted && $this->isCookieLawCountry();

        $this->logActiveSession();

        return $this;
    }

    public function getThemes(): array
    {
        $themes = app(CssTheme::class)
            ->where('default_dark', true)
            ->orWhere('default_light', true)
            ->get();

        $selectedTheme = null;

        // first, get theme from cookie or url param, if theme change by user is enabled
        if ($this->settings->get('themes.user_change')) {
            if ($themeFromUrl = $this->request->get('beThemeId')) {
                $selectedTheme = $themes->find($themeFromUrl);
            } else {
                $selectedTheme = $themes->find(
                    Arr::get($_COOKIE, 'be-active-theme'),
                );
            }
        }

        // if no theme was selected, get default theme specified by admin
        if (
            !$selectedTheme &&
            ($defaultId = $this->settings->get('themes.default_id'))
        ) {
            $selectedTheme = $themes->find($defaultId);
        }

        // finally, fallback to default light theme
        if (!$selectedTheme) {
            $selectedTheme = $themes->where('default_light', true)->first();
        }

        return [
            'all' => $themes,
            'selectedThemeId' => $selectedTheme?->id,
        ];
    }

    /**
     * Load current user and his roles.
     */
    public function getCurrentUser(): ?User
    {
        $user = $this->request->user();
        if ($user) {
            // load user subscriptions, if billing is enabled
            if (
                app(Settings::class)->get('billing.enable') &&
                !$user->relationLoaded('subscriptions')
            ) {
                $user->load('subscriptions.price');
            }

            // load user roles, if not already loaded
            if (!$user->relationLoaded('roles')) {
                $user->load('roles');
            }

            if (!$user->relationLoaded('permissions')) {
                $user->loadPermissions();
            }
        }

        return $user;
    }

    protected function getDefaultMetaTags(): array
    {
        $namespace = 'home.show';
        $meta = new MetaTags(config("seo.$namespace"), [], $namespace);
        return $meta->toArray();
    }

    protected function isCookieLawCountry(): bool
    {
        $isoCode = geoip(getIp())['iso_code'];
        // prettier-ignore
        return in_array($isoCode, ['AT', 'BE', 'BG', 'BR', 'CY', 'CZ', 'DE', 'DK', 'EE', 'EL', 'ES', 'FI', 'FR', 'GB', 'HR', 'HU', 'IE', 'IT','LT', 'LU', 'LV', 'MT', 'NL', 'NO', 'PL', 'PT', 'RO', 'SE', 'SI', 'SK',
        ]);
    }

    protected function logActiveSession()
    {
        if ($this->data['user']) {
            LogActiveSessionJob::dispatch([
                'user_id' => $this->data['user']->id,
                'ip_address' => getIp(),
                'user_agent' => $this->request->userAgent(),
                'session_id' => session()->getId(),
                'token' => $this->data['user']->currentAccessToken()?->token,
            ]);
        }
    }
}
