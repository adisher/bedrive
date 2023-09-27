<?php namespace Common\Admin\Appearance;

use Common\Admin\Appearance\Themes\CssTheme;
use Common\Core\Prerender\MetaTags;
use Common\Settings\Settings;
use Exception;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\File;

class AppearanceValues
{
    /**
     * ENV values to include.
     */
    const ENV_KEYS = ['app_name'];

    public function get(): array
    {
        // split values into db settings and appearance specific settings, to avoid naming collisions
        $values = [
            'settings' => app(Settings::class)->getUnflattened(),
            'appearance' => [],
        ];

        // add env settings
        $values['appearance']['env'] = [];
        foreach (self::ENV_KEYS as $key) {
            $values['appearance']['env'][$key] = config(
                str_replace('_', '.', $key),
            );
        }

        $values['appearance']['themes'] = [
            'all' => CssTheme::get(),
            'selectedThemeId' => null,
        ];

        // add custom code
        $values['appearance']['custom_code'] = [
            'css' => $this->getCustomCodeValue(
                AppearanceSaver::CUSTOM_CSS_PATH,
            ),
            'html' => $this->getCustomCodeValue(
                AppearanceSaver::CUSTOM_HTML_PATH,
            ),
        ];

        $values['appearance']['seo'] = $this->prepareSeoValues();

        $defaults = [];
        $defaultSettings = collect(config('common.default-settings'))
            ->mapWithKeys(fn($item) => [$item['name'] => $item['value']])
            ->toArray();
        $defaults['settings'] = app(Settings::class)->getUnflattened(
            false,
            $defaultSettings,
        );
        $defaults['appearance']['themes'] = config('common.themes');

        return [
            'values' => $values,
            'defaults' => $defaults,
        ];
    }

    private function prepareSeoValues(): array
    {
        $flat = [];
        $seoConfig = config('seo');

        if (!$seoConfig) {
            return [];
        }

        $seo = Arr::except($seoConfig, 'common');
        $seo = array_filter($seo, function ($config) {
            return is_array($config);
        });

        // resource groups meta tags for artist, movie, track etc.
        foreach ($seo as $resourceName => $resource) {
            // resource has config for each verb (show, index etc.)
            foreach ($resource as $verbName => $verb) {
                // verb has a list of meta tags (og:title, description etc.)
                if (is_array($verb)) {
                    foreach ($verb as $metaTag) {
                        $property = Arr::get($metaTag, 'property');
                        if (!in_array($property, MetaTags::EDITABLE_TAGS)) {
                            continue;
                        }

                        $name = str_replace(
                            'og:',
                            '',
                            "$resourceName / $verbName / $property",
                        );
                        $name = str_replace('-', ' ', $name);

                        $key = "seo.$resourceName.$verbName.$property";
                        $defaultValue = $metaTag['content'];

                        $flat[] = [
                            'name' => $name,
                            'key' => $key,
                            'value' => app(Settings::class)->get(
                                $key,
                                $defaultValue,
                            ),
                            'defaultValue' => $defaultValue,
                        ];
                    }
                }
            }
        }

        return $flat;
    }

    private function getCustomCodeValue($path): string
    {
        try {
            return File::get(public_path("storage/$path"));
        } catch (Exception $e) {
            return '';
        }
    }
}
