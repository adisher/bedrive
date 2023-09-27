<?php namespace Common\Localizations;

use Arr;
use Illuminate\Filesystem\Filesystem;

class LocalizationsRepository
{
    /**
     * @var Filesystem
     */
    private $fs;

    /**
     * @var Localization
     */
    private $localization;

    /**
     * Path to files with default localization language lines.
     */
    const DEFAULT_TRANS_PATHS = [
        'client-translations.json',
        'server-translations.json',
    ];

    public function __construct(Filesystem $fs, Localization $localization)
    {
        $this->fs = $fs;
        $this->localization = $localization;
    }

    public function getByNameOrCode(
        string $name,
        bool $loadLines = true,
    ): ?Localization {
        $localization = $this->localization
            ->where('name', $name)
            ->orWhere('language', $name)
            ->first();
        if (!$localization) {
            return null;
        }
        if ($loadLines) {
            $localization->loadLines();
        }
        return $localization;
    }

    public function update(
        int $id,
        array $data,
        $overrideLines = false,
    ): ?Localization {
        $localization = Localization::findOrFail($id);
        $localization->updated_at = now();
        $language = Arr::get($data, 'language');

        if (isset($data['name']) && $data['name'] !== $localization->name) {
            $localization->name = $data['name'];
        }

        if ($language && $language !== $localization->language) {
            $this->renameLocalizationLinesFile($localization, $language);
            $localization->language = $language;
        }

        if (isset($data['lines']) && $data['lines']) {
            $this->storeLocalizationLines(
                $localization,
                $data['lines'],
                $overrideLines,
            );
        }

        $localization->save();

        return $localization;
    }

    public function create(array $params): ?Localization
    {
        $localization = $this->localization->create([
            'name' => $params['name'],
            'language' => $params['language'],
        ]);

        $lines = $this->getDefaultTranslationLines();
        $this->storeLocalizationLines($localization, $lines);

        return $localization;
    }

    public function delete(int $id): bool
    {
        $localization = $this->localization->findOrFail($id);

        $this->fs->delete($this->makeLocalizationLinesPath($localization));

        return $localization->delete();
    }

    /**
     * Get default translations lines for the application.
     */
    public function getDefaultTranslationLines(): array
    {
        $combined = [];

        foreach (self::DEFAULT_TRANS_PATHS as $path) {
            if (!$this->fs->exists(resource_path($path))) {
                continue;
            }
            $combined = array_merge(
                $combined,
                json_decode($this->fs->get(resource_path($path)), true),
            );
        }

        return $combined;
    }

    public function storeLocalizationLines(
        Localization $localization,
        $newLines,
        $override = false,
    ) {
        $path = $this->makeLocalizationLinesPath($localization);
        $oldLines = [];

        if (!$override && file_exists($path)) {
            $oldLines = json_decode(file_get_contents($path), true);
        }

        $merged = array_merge($oldLines, $newLines);

        return file_put_contents(
            $path,
            json_encode($merged, JSON_UNESCAPED_UNICODE),
        );
    }

    public function getLocalizationLines(Localization $localization): array
    {
        $path = $this->makeLocalizationLinesPath($localization);

        if (file_exists($path)) {
            return json_decode(file_get_contents($path), true);
        } else {
            return [];
        }
    }

    public function makeLocalizationLinesPath(Localization $localization)
    {
        return resource_path("lang/$localization->language.json");
    }

    public function renameLocalizationLinesFile(
        Localization $localization,
        string $newLangCode,
    ): bool {
        $oldPath = $this->makeLocalizationLinesPath($localization);
        $newPath = resource_path("lang/$newLangCode.json");
        return $this->fs->move($oldPath, $newPath);
    }
}
