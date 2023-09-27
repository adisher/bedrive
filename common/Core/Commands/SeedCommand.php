<?php namespace Common\Core\Commands;

use File;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Seeder;
use Str;

class SeedCommand extends Command
{
    protected $signature = 'common:seed';

    protected $description = 'Execute all common package seeders.';

    public function handle()
    {
        $paths = collect(File::files(__DIR__ . '/../../Database/Seeds'));

        $paths->filter(function($path) {
            return Str::endsWith($path, '.php');
        })->each(function($path) {
            Model::unguarded(function () use ($path) {
                $namespace = 'Common\Database\Seeds\\'.basename($path, '.php');
                $this->getSeeder($namespace)->__invoke();
            });
        });

        $this->info('Seeded database successfully.');
    }
    
    protected function getSeeder(string $namespace): Seeder
    {
        $class = $this->laravel->make($namespace);

        return $class->setContainer($this->laravel)->setCommand($this);
    }
}
