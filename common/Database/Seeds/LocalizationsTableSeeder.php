<?php namespace Common\Database\Seeds;

use Common\Localizations\Localization;
use Common\Localizations\LocalizationsRepository;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Seeder;

class LocalizationsTableSeeder extends Seeder
{
    public function __construct(protected LocalizationsRepository $repository)
    {
    }

    public function run()
    {
        $localizations = Localization::all();

        if ($localizations->isNotEmpty()) {
            $this->mergeExistingTranslationLines($localizations);
        } else {
            $this->repository->create([
                'name' => 'English',
                'language' => 'en',
            ]);
        }
    }

    /**
     * Merge existing localization translation lines with default ones.
     */
    private function mergeExistingTranslationLines(Collection $localizations)
    {
        $defaultLines = $this->repository->getDefaultTranslationLines();

        $localizations->each(function ($localization) use ($defaultLines) {
            $this->repository->storeLocalizationLines(
                $localization,
                array_merge(
                    $defaultLines,
                    $this->repository->getLocalizationLines($localization),
                ),
            );
        });
    }
}
