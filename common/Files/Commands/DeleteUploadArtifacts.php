<?php

namespace Common\Files\Commands;

use Common\Files\Actions\Deletion\PermanentlyDeleteEntries;
use Common\Files\FileEntry;
use Common\Settings\Settings;
use DB;
use Illuminate\Console\Command;
use Schema;
use Storage;
use Str;

class DeleteUploadArtifacts extends Command
{
    protected array $map = [
        'branding_media' => [
            'type' => 'settings',
            'keys' => [
                'branding.logo_light',
                'branding.logo_dark',
                'logo_light_mobile',
                'logo_dark_mobile',
            ],
        ],
        'homepage_media' => [
            'type' => 'settings',
            'keys' => ['homepage.appearance'],
        ],
        'page_media' => [
            'type' => 'model',
            'table' => 'custom_pages',
            'column' => 'body',
        ],

        // mtdb
        'title-videos' => [
            'type' => 'model',
            'table' => 'videos',
            'column' => 'src',
        ],
        'media-images/videos' => [
            'type' => 'model',
            'table' => 'videos',
            'column' => 'thumbnail',
        ],

        // bemusic
        'track_image_media' => [
            'type' => 'model',
            'table' => 'tracks',
            'column' => 'image',
        ],
        'album_media' => [
            'type' => 'model',
            'table' => 'albums',
            'column' => 'image',
        ],
        'track_media' => [
            'type' => 'model',
            'table' => 'tracks',
            'column' => 'src',
        ],
        'artist_media' => [
            'type' => 'model',
            'table' => 'artists',
            'column' => 'image_small',
        ],
        'genre_media' => [
            'type' => 'model',
            'table' => 'genres',
            'column' => 'image',
        ],
        'playlist_media' => [
            'type' => 'model',
            'table' => 'playlists',
            'column' => 'image',
        ],

        // bedesk
        'ticket_images' => [
            'type' => 'model',
            'table' => 'replies',
            'column' => 'body',
        ],
        'article_images' => [
            'type' => 'model',
            'table' => 'articles',
            'column' => 'body',
        ],

        // belink
        'link_overlay_images' => [
            'type' => 'model',
            'table' => 'link_overlays',
            'column' => 'colors',
        ],
    ];

    /**
     * @var string
     */
    protected $signature = 'uploads:clean';

    /**
     * @var string
     */
    protected $description = 'Delete unused files that were uploaded via various application pages.';

    /**
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {
        $storage = Storage::disk('public');
        $count = 0;
        foreach ($this->map as $folder => $config) {
            if ($storage->exists($folder)) {
                $fileNames = collect($storage->allFiles($folder))
                    ->filter(fn($path) => $this->shouldDelete($path, $config))
                    ->map(fn($path) => basename($path));
                $count += $fileNames->count();
                $entryIds = FileEntry::whereIn('file_name', $fileNames)->pluck(
                    'id',
                );
                app(PermanentlyDeleteEntries::class)->execute($entryIds);
            }
        }

        $this->info("Deleted $count unused files.");
    }

    protected function shouldDelete(string $path, array $config): bool
    {
        if ($config['type'] === 'settings') {
            return collect($config['keys'])
                ->map(fn($key) => app(Settings::class)->get($key))
                ->filter(
                    fn($configValue) => Str::contains(
                        $configValue,
                        basename($path),
                    ),
                )
                ->isEmpty();
        } elseif ($config['type'] === 'model') {
            if (Schema::hasTable($config['table'])) {
                $fileName = basename($path);
                return DB::table($config['table'])
                    ->whereNotNull($config['column'])
                    ->where($config['column'], 'like', "%$fileName%")
                    ->count() === 0;
            }
        }
    }
}
