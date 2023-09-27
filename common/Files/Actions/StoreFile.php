<?php

namespace Common\Files\Actions;

use Common\Files\FileEntryPayload;
use Illuminate\Contracts\Filesystem\Filesystem;
use Illuminate\Http\File;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\File as FileFacade;
use Illuminate\Support\Str;
use League\Flysystem\Local\LocalFilesystemAdapter;
use Storage;
use Symfony\Component\Mime\MimeTypes;

class StoreFile
{
    protected Filesystem $disk;
    protected array $diskOptions;
    protected FileEntryPayload $payload;

    public function execute(
        FileEntryPayload $payload,
        array $fileOptions,
    ): string|false {
        $this->disk = $payload->public
            ? Storage::disk('public')
            : Storage::disk('uploads');

        $this->diskOptions = [
            'mimetype' => $payload->clientMime,
            'visibility' => $payload->visibility,
        ];

        $this->payload = $payload;

        if (
            // prevent uploading .htaccess files
            $payload->filename === '.htaccess' ||
            // dont store php files in public disk
            ($payload->public && $this->isPhpFile($payload, $fileOptions)) ||
            // prevent path traversal in user specified folder
            ($payload->diskPrefix && Str::contains($payload->diskPrefix, '..'))
        ) {
            abort(403);
        }

        if (isset($fileOptions['file'])) {
            return $this->storeUploadedFile($fileOptions['file']);
        } elseif (isset($fileOptions['contents'])) {
            return $this->storeStringContents($fileOptions['contents']);
        } elseif (isset($fileOptions['path'])) {
            // if source and destination is local (and not temp dir) move file
            // instead of copying or using streams, this will be a lot faster
            if (
                Arr::get($fileOptions, 'moveFile') === true &&
                $this->disk->getAdapter() instanceof LocalFilesystemAdapter
            ) {
                return $this->storeLocalFile($fileOptions['path']);
            } else {
                return $this->storeUploadedFile(new File($fileOptions['path']));
            }
        }

        return false;
    }

    protected function storeUploadedFile(File|UploadedFile $file): string|false
    {
        return $this->disk->putFileAs(
            $this->payload->diskPrefix,
            $file,
            $this->payload->filename,
            $this->diskOptions,
        );
    }

    protected function storeStringContents(string $contents): string|false
    {
        return $this->disk->put(
            "{$this->payload->diskPrefix}/{$this->payload->filename}",
            $contents,
            $this->diskOptions,
        );
    }

    protected function storeLocalFile(string $sourcePath): string|false
    {
        $dirPath = $this->disk->path($this->payload->diskPrefix);

        FileFacade::ensureDirectoryExists($dirPath);
        $stored = @rename($sourcePath, "$dirPath/{$this->payload->filename}");

        if ($stored) {
            return "{$this->payload->diskPrefix}/{$this->payload->filename}";
        }

        return false;
    }

    protected function isPhpFile(
        FileEntryPayload $payload,
        array $fileOptions,
    ): bool {
        if ($payload->clientExtension === 'php') {
            return true;
        }

        $mimeType = null;
        if (isset($fileOptions['file'])) {
            $mimeType = $fileOptions['file']->getMimeType();
        } elseif (isset($fileOptions['path'])) {
            $mimeType = MimeTypes::getDefault()->guessMimeType(
                $fileOptions['path'],
            );
        }

        return $mimeType === 'application/x-php';
    }
}
