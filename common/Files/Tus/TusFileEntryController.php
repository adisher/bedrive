<?php

namespace Common\Files\Tus;

use Common\Core\BaseController;
use Common\Files\Actions\CreateFileEntry;
use Common\Files\Actions\StoreFile;
use Common\Files\Events\FileUploaded;
use Common\Files\FileEntry;
use Common\Files\FileEntryPayload;
use Illuminate\Support\Facades\File;

class TusFileEntryController extends BaseController
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function store()
    {
        $data = $this->validate(request(), [
            'uploadKey' => 'required|string',
        ]);

        $tusData = app(TusCache::class)->get($data['uploadKey']);

        if (!$tusData) {
            return $this->error();
        }

        $metadata = $tusData['metadata'];
        $tusFilePath = $tusData['file_path'];
        $metadata['size'] = $tusData['size'];
        // tus temp file fingerprint, not needed anymore
        unset($metadata['name']);

        $payload = new FileEntryPayload($metadata);

        $this->authorize('store', [FileEntry::class, $payload->parentId]);

        app(StoreFile::class)->execute($payload, [
            'path' => $tusFilePath,
            'moveFile' => true,
        ]);

        $fileEntry = app(CreateFileEntry::class)->execute($payload);
        event(new FileUploaded($fileEntry));
        File::delete($tusFilePath);
        return $this->success(['fileEntry' => $fileEntry]);
    }
}
