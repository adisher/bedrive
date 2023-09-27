<?php

namespace Common\Files\Tus;

use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\File;

class DeleteExpiredTusUploads extends Command
{
    protected $signature = 'tus:expired';

    protected $description = 'Deletes expired tus uploads.';

    public function handle(): int
    {
        $cache = app(TusCache::class);

        $directory = storage_path('tus');

        if (File::exists($directory)) {
            foreach (File::allFiles(storage_path('tus')) as $file) {
                $uploadKey = $file->getFilename();
                $tusData = $cache->get($uploadKey);
                if (
                    !Arr::get($tusData, 'expires_at') ||
                    Carbon::parse($tusData['expires_at'])->lt(Carbon::now())
                ) {
                    $cache->delete($uploadKey);
                    File::delete($file);
                }
            }
        }

        $this->info('Uploads deleted!');

        return Command::SUCCESS;
    }
}
