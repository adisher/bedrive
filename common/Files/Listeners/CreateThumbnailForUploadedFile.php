<?php

namespace Common\Files\Listeners;

use Common\Files\Events\FileUploaded;
use Common\Files\FileEntry;
use Exception;
use Illuminate\Contracts\Queue\ShouldQueue;
use Intervention\Image\Constraint;
use Intervention\Image\Facades\Image;

class CreateThumbnailForUploadedFile implements ShouldQueue
{
    public function handle(FileUploaded $event)
    {
        // only create thumbnail for images over 500KB in size
        if (
            !$event->fileEntry->public &&
            $event->fileEntry->type === 'image' &&
            $event->fileEntry->file_size > 500000 &&
            !config('common.site.disable_thumbnail_creation')
        ) {
            try {
                $this->maybeCreateThumbnail($event->fileEntry);
            } catch (Exception $e) {
                //
            }
        }
    }

    private function maybeCreateThumbnail(FileEntry $entry)
    {
        $this->setMemoryLimit();
        $file = $entry->getDisk()->readStream($entry->getStoragePath());
        $img = Image::make($file)->orientate();

        $img->fit(350, 250, function (Constraint $constraint) {
            $constraint->upsize();
        });

        $img->encode($entry->extension === 'png' ? 'png' : 'jpg', 60);

        $entry->getDisk()->put("{$entry->file_name}/thumbnail.jpg", $img, [
            'mimetype' => $img->mime(),
            'visibility' => config('common.site.remote_file_visibility'),
        ]);

        $entry->fill(['thumbnail' => true])->save();
    }

    private function setMemoryLimit()
    {
        $new = 512;
        $current = (int) ini_get('memory_limit');
        if ($current < $new) {
            @ini_set('memory_limit', "{$new}M");
        }
    }
}
