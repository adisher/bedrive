<?php namespace Common\Localizations\Commands;

use App\User;
use Common\Auth\Permissions\Permission;
use Common\Core\Values\ValueLists;
use Error;
use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use SplFileInfo;
use Symfony\Component\Finder\Finder;

class ExportTranslations extends Command
{
    protected $signature = 'translations:export';

    protected $description = 'Export default laravel translations as flattened json file.';

    public function __construct(protected Filesystem $fs)
    {
        parent::__construct();
    }

    public function handle(): void
    {
        $messages = array_merge(
            $this->getLaravelTranslationMessages(),
            $this->getDefaultMenuLabels(),
            $this->getPermissionNamesAndDescriptions(),
        );

        $messages = array_filter(
            $messages,
            fn($value, $key) => $value && $key,
            ARRAY_FILTER_USE_BOTH,
        );

        $this->fs->put(
            resource_path('server-translations.json'),
            json_encode($messages),
        );

        $this->info('Translation lines exported as json.');
    }

    protected function getLaravelTranslationMessages()
    {
        $files = collect(
            (new Finder())
                ->in([
                    base_path('app'),
                    base_path('common'),
                    resource_path('views'),
                    resource_path('lang/en'),
                    base_path('vendor/laravel'),
                ])
                ->name(['*.php'])
                ->files(),
        );

        $lines = $files
            ->map(function (SplFileInfo $file) {
                $functions = ['__', 'trans', '@lang', 'Lang::get'];
                $lines = [];
                $contents = $file->getContents();

                if (Str::contains($contents, 'extends BaseFormRequest')) {
                    $lines = array_merge(
                        $lines,
                        $this->getCustomValidationMessages($file),
                    );
                }

                foreach ($functions as $function) {
                    if (
                        preg_match_all(
                            '/(' .
                                $function .
                                ')\([\r\n\s]{0,}\h*[\'"](.+)[\'"]\h*[\r\n\s]{0,}[),]/U',
                            $file->getContents(),
                            $matches,
                        )
                    ) {
                        $lines[] = $matches[2];
                    }
                }

                return $lines;
            })
            ->flatten()
            ->map(fn(string $string) => stripslashes($string))
            ->unique()
            ->filter(function (string $string) {
                // ignore laravel short translation keys
                // (pagination.next for example) and only use json keys
                return !preg_match('/^[^.\s]\S*\.\S*[^.\s]$/', $string);
            });

        return $lines->combine($lines)->toArray();
    }

    private function getDefaultMenuLabels(): array
    {
        $menus = Arr::first(
            config('common.default-settings'),
            fn($setting) => $setting['name'] === 'menus',
        );

        if ($menus) {
            return collect(json_decode($menus['value'], true))
                ->pluck('items.*.label')
                ->flatten()
                ->mapWithKeys(fn($key) => [$key => $key])
                ->toArray();
        }

        return [];
    }

    /**
     * Get custom validation messages from Laravel Request files.
     */
    private function getCustomValidationMessages(SplFileInfo $file): array
    {
        //make namespace from file path
        $namespace = str_replace(
            [base_path() . DIRECTORY_SEPARATOR, '.php'],
            '',
            $file->getPathname(),
        );
        $namespace = ucfirst(str_replace('/', '\\', $namespace));

        $messages = [];
        try {
            foreach ((new $namespace())->messages() as $message) {
                $messages[$message] = $message;
            }
        } catch (Error $e) {
            //
        }

        return $messages;
    }

    private function getPermissionNamesAndDescriptions(): array
    {
        Auth::login(User::findAdmin());
        $lines = app(ValueLists::class)
            ->permissions()
            ->map(function (Permission $permission) {
                $restrictionLines = $permission->restrictions
                    ->map(function ($restriction) {
                        return [
                            ucfirst(str_replace('_', ' ', $restriction['name'])),
                            $restriction['description'],
                        ];
                    })
                    ->flatten()
                    ->toArray();

                return [
                    $permission['display_name'],
                    $permission['group'],
                    $permission['description'],
                    ...$restrictionLines,
                ];
            })
            ->flatten(1)
            ->unique()
            ->toArray();

        return array_combine($lines, $lines);
    }
}
