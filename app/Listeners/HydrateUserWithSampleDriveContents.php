<?php

namespace App\Listeners;

use App\Folder;
use App\Services\Entries\CreateFolder;
use App\Services\Shares\AttachUsersToEntry;
use Common\Auth\Events\UserCreated;
use Common\Files\Actions\CreateFileEntry;
use Common\Files\Actions\StoreFile;
use Common\Files\FileEntryPayload;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Http\UploadedFile;

class HydrateUserWithSampleDriveContents
{
    public function __construct(private Filesystem $fs)
    {
    }

    public function handle(UserCreated $event): void
    {
        $user = $event->user;

        $this->hydrateFolder('root', $user->id);

        $images = $this->hydrateFolder('images', $user->id);
        $this->hydrateFolder('nested folder', $user->id, $images->id);

        $this->hydrateFolder('documents', $user->id);

        $folder = $this->hydrateFolder('shared', $user->id);
        app(AttachUsersToEntry::class)->execute(
            ['tester@tester.com'],
            [$folder],
            ['view' => true],
        );
    }

    private function hydrateFolder($name, $userId, $parentId = null): ?Folder
    {
        if ($name !== 'root') {
            $folder = app()
                ->make(CreateFolder::class)
                ->execute([
                    'name' => ucwords($name),
                    'ownerId' => $userId,
                    'parentId' => $parentId,
                ]);
        }

        $this->createFiles($name, isset($folder) ? $folder->id : null, $userId);

        return $folder ?? null;
    }

    private function createFiles($dirName, $parentId, $userId): void
    {
        $folderPath = base_path("sample-files/$dirName");

        if (!$this->fs->exists($folderPath)) {
            return;
        }

        foreach ($this->fs->files($folderPath) as $path) {
            $uploadedFile = new UploadedFile(
                $path,
                basename($path),
                $this->fs->mimeType($path),
                $this->fs->size($path),
            );

            $payload = new FileEntryPayload([
                'file' => $uploadedFile,
                'parentId' => $parentId,
                'ownerId' => $userId,
            ]);

            app(StoreFile::class)->execute($payload, ['file' => $uploadedFile]);
            app(CreateFileEntry::class)->execute($payload);
        }
    }
}
