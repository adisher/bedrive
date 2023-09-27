<?php

namespace Common\Files\S3;

use Common\Core\BaseController;
use Common\Files\Actions\CreateFileEntry;
use Common\Files\Events\FileUploaded;
use Common\Files\FileEntry;
use Common\Files\FileEntryPayload;

class S3FileEntryController extends BaseController
{
    public function store()
    {
        $validatedData = $this->validate(request(), [
            'clientExtension' => 'required|string',
            'clientMime' => 'nullable|string|max:255',
            'clientName' => 'required|string',
            'disk' => 'string',
            'diskPrefix' => 'string',
            'filename' => 'required|string',
            'parentId' => 'nullable|exists:file_entries,id',
            'relativePath' => 'nullable|string',
            'workspaceId' => 'nullable|int',
            'size' => 'required|int',
        ]);

        $payload = new FileEntryPayload($validatedData);

        $this->authorize('store', [FileEntry::class, $payload->parentId]);

        $fileEntry = app(CreateFileEntry::class)->execute($payload);

        event(new FileUploaded($fileEntry));

        return $this->success(['fileEntry' => $fileEntry]);
    }
}
