<?php namespace Common\Core\Values;

use Common\Admin\Appearance\Themes\CssTheme;
use Common\Auth\Permissions\Permission;
use Common\Auth\Roles\Role;
use Common\Domains\CustomDomain;
use Common\Files\FileEntry;
use Common\Localizations\Localization;
use Common\Pages\CustomPage;
use Illuminate\Contracts\Auth\Access\Gate;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use const App\Providers\WORKSPACED_RESOURCES;

class ValueLists
{
    public function __construct(
        protected Filesystem $fs,
        protected Localization $localization
    ) {
    }

    public function get(string $names, array $params = []): Collection
    {
        return collect(explode(',', $names))
            ->mapWithKeys(function ($name) use ($params) {
                $methodName = Str::studly($name);
                $value = method_exists($this, $methodName)
                    ? $this->$methodName($params)
                    : $this->loadAppValueFile($name, $params);
                return [$name => $value];
            })
            ->filter();
    }

    public function permissions(): Collection
    {
        $query = app(Permission::class)->where('type', 'sitewide');

        if (!config('common.site.enable_custom_domains')) {
            $query->where('group', '!=', 'custom_domains');
        }

        if (!config('common.site.notifications_integrated')) {
            $query->where('group', '!=', 'notifications');
        }

        // TODO: fetch and merge advanced and description from config files here
        // instead of storing in db. Then can override workspace descriptions and
        // advanced state easily here from separate config file

        if (!config('common.site.workspaces_integrated')) {
            $query
                ->where('group', '!=', 'workspaces')
                ->where('group', '!=', 'workspace_members');
        }

        if (!config('common.site.billing_integrated')) {
            $query
                ->where('group', '!=', 'plans')
                ->where('group', '!=', 'invoices');
        }

        if (!Auth::user() || !Auth::user()->hasExactPermission('admin')) {
            $query->where('name', '!=', 'admin');
        }

        return $query->get();
    }

    public function roles(): Collection
    {
        return app(Role::class)
            ->where('type', 'sitewide')
            ->get();
    }

    public function workspaceRoles(): Collection
    {
        return app(Role::class)
            ->where('type', 'workspace')
            ->get();
    }

    public function workspacePermissions($params = []): Collection
    {
        $filters = array_map(function ($resource) {
            if (is_subclass_of($resource, FileEntry::class)) {
                return 'files';
            } elseif (is_subclass_of($resource, CustomDomain::class)) {
                return 'custom_domains';
            } else {
                return Str::snake(Str::pluralStudly(class_basename($resource)));
            }
        }, WORKSPACED_RESOURCES);

        return app(Permission::class)
            ->where('type', 'workspace')
            ->orWhere(function (Builder $builder) use ($filters) {
                $builder->where('type', 'sitewide')->whereIn('group', $filters);
            })
            // don't return restrictions for workspace permissions so they
            // are not shown when creating workspace role from admin area
            ->get([
                'id',
                'name',
                'display_name',
                'description',
                'group',
                'type',
            ])
            ->map(function (Permission $permission) {
                $permission->description = str_replace(
                    'ALL',
                    'all workspace',
                    $permission->description,
                );
                $permission->description = str_replace(
                    'creating new',
                    'creating new workspace',
                    $permission->description,
                );
                return $permission;
            });
    }

    public function currencies()
    {
        return json_decode(
            $this->fs->get(__DIR__ . '/../../resources/lists/currencies.json'),
            true,
        );
    }

    public function timezones()
    {
        return json_decode(
            $this->fs->get(__DIR__ . '/../../resources/lists/timezones.json'),
            true,
        );
    }

    public function countries()
    {
        return json_decode(
            $this->fs->get(__DIR__ . '/../../resources/lists/countries.json'),
            true,
        );
    }

    public function languages()
    {
        return json_decode(
            $this->fs->get(__DIR__ . '/../../resources/lists/languages.json'),
            true,
        );
    }

    public function localizations()
    {
        return $this->localization->get(['id', 'name', 'language']);
    }

    public function googleFonts(): array
    {
        $googleFonts = json_decode(
            $this->fs->get(
                __DIR__ . '/../../resources/lists/google-fonts.json',
            ),
            true,
        );
        return array_map(function ($font) {
            return [
                'family' => $font['family'],
                'category' => $font['category'],
                'google' => true,
            ];
        }, $googleFonts);
    }

    public function menuItemCategories(): array
    {
        return array_map(function ($category) {
            $category['items'] = app($category['itemsLoader'])->execute();
            unset($category['itemsLoader']);
            return $category;
        }, config('common.menus'));
    }

    public function pages($params = [])
    {
        if (!isset($params['userId'])) {
            app(Gate::class)->authorize('index', CustomPage::class);
        }

        $query = app(CustomPage::class)
            ->select(['id', 'title'])
            ->where(
                'type',
                Arr::get($params, 'pageType') ?: CustomPage::PAGE_TYPE,
            );

        if ($userId = Arr::get($params, 'userId')) {
            $query->where('user_id', $userId);
        }

        return $query->get();
    }

    public function domains(Collection|array $params): Collection
    {
        return app(CustomDomain::class)
            ->select(['host', 'id'])
            ->where('user_id', Arr::get($params, 'userId'))
            ->orWhere('global', true)
            ->get();
    }

    public function themes(Collection|array $params): Collection
    {
        app(Gate::class)->authorize('index', CssTheme::class);
        return app(CssTheme::class)
            ->select(['name', 'id'])
            ->get();
    }

    private function loadAppValueFile(string $name, array $params): ?array
    {
        $fileName = Str::kebab($name);
        $path = resource_path("lists/$fileName.json");
        if (file_exists($path)) {
            return json_decode(file_get_contents($path), true);
        }
        return null;
    }
}
