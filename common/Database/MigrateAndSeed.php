<?php

namespace Common\Database;

use Common\Admin\Appearance\GenerateFavicon;
use Common\Core\Manifest\BuildManifestFile;
use Database\Seeders\DatabaseSeeder;
use File;
use Illuminate\Database\Eloquent\Model;

class MigrateAndSeed
{
    public function execute(callable $afterMigrateCallback = null): void
    {
        // Migrate
        if (!app('migrator')->repositoryExists()) {
            app('migration.repository')->createRepository();
        }
        $migrator = app('migrator');
        $paths = $migrator->paths();
        $paths[] = app('path.database') . DIRECTORY_SEPARATOR . 'migrations';
        $migrator->run($paths);

        $afterMigrateCallback && $afterMigrateCallback();

        $this->runCommonSeeders();

        // Seed
        $seeder = class_exists(\DatabaseSeeder::class)
            ? app(\DatabaseSeeder::class)
            : app(DatabaseSeeder::class);
        $seeder->setContainer(app());
        Model::unguarded(function () use ($seeder) {
            $seeder->__invoke();
        });

        // Manifest
        app(BuildManifestFile::class)->execute();

        $defaultFaviconPath = public_path('images/favicon-original.png');
        if (!env('INSTALLED') && file_exists($defaultFaviconPath)) {
            app(GenerateFavicon::class)->execute($defaultFaviconPath);
        }
    }

    public function runCommonSeeders(): void
    {
        $paths = File::files(app('path.common') . '/Database/Seeds');
        foreach ($paths as $fileInfo) {
            Model::unguarded(function () use ($fileInfo) {
                $namespace =
                    'Common\Database\Seeds\\' . $fileInfo->getBaseName('.php');
                $seeder = app($namespace)->setContainer(app());
                $seeder->__invoke();
            });
        }
    }
}
