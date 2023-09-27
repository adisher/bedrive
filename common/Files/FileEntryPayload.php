<?php

namespace Common\Files;

use Arr;
use Common\Files\Traits\GetsEntryTypeFromMime;
use Common\Settings\Settings;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Auth;
use Str;

class FileEntryPayload
{
    use GetsEntryTypeFromMime;

    private array $data;
    public mixed $diskName;
    public string $clientName;
    public string $filename;
    public ?int $workspaceId;
    public string $clientMime;
    public string $type;
    public ?string $relativePath;
    public string $clientExtension;

    public int $size;
    public ?int $parentId;

    public string $diskPrefix;
    public bool $public;
    public string $visibility;
    public int|null $ownerId;

    public function __construct(array $data)
    {
        $this->prepareData($data);
        $this->diskName =  Arr::get($data, 'disk', 'uploads');
        $this->public = $this->diskName === 'public';
        $this->prepareEntryPayload();
    }

    protected function prepareData(array $data): void
    {
        $file = Arr::get($data, 'file');
        $this->data = Arr::except($data, 'file');
        if ($file instanceof UploadedFile) {
            $this->data['clientName'] = $file->getClientOriginalName();
            $this->data['clientMime'] = $file->getClientMimeType();
            $this->data['size'] = $file->getSize();
            $this->data[
                'clientExtension'
            ] = $file->getClientOriginalExtension();
        }
    }

    protected function prepareEntryPayload(): void
    {
        $this->clientName = $this->data['clientName'];
        $this->clientMime = $this->data['clientMime'];
        $this->clientExtension = $this->getExtension();
        $this->filename = $this->getFilename();
        $this->workspaceId = Arr::has($this->data, 'workspaceId')
            ? (int) $this->data['workspaceId']
            : null;
        $this->relativePath = $this->getRelativePath();
        $this->diskPrefix = $this->getDiskPrefix();
        $this->parentId = (int) Arr::get($this->data, 'parentId') ?: null;
        $this->ownerId = (int) Arr::get($this->data, 'ownerId') ?: Auth::id();
        $this->size =
            $this->data['file_size'] ??
            ($this->data['size'] ?? $this->data['clientSize']);
        $this->visibility = $this->public
            ? 'public'
            : config('common.site.remote_file_visibility');
        $this->type = $this->getTypeFromMime(
            $this->clientMime,
            $this->clientExtension,
        );
    }

    private function getDiskPrefix()
    {
        if ($this->public) {
            return Arr::get($this->data, 'diskPrefix');
        } else {
            return $this->filename;
        }
    }

    private function getFilename()
    {
        $keepOriginalName = app(Settings::class)->get(
            'uploads.keep_original_name',
        );

        if (isset($this->data['filename'])) {
            return $this->data['filename'];
        }

        $uuid = Str::uuid();

        // public files will be stored with extension
        if ($this->public) {
            return $keepOriginalName
                ? $this->clientName
                : "{$uuid}.{$this->clientExtension}";
        } else {
            return $uuid;
        }
    }

    private function getRelativePath(): string|null
    {
        // relative path might sometimes be "null" or "false" as string
        $relativePath = Arr::get($this->data, 'relativePath');
        if (!is_string($relativePath) || !Str::contains($relativePath, '/')) {
            $relativePath = null;
        }
        return $relativePath;
    }

    private function getExtension(): string
    {
        if ($extension = Arr::get($this->data, 'clientExtension')) {
            return $extension;
        }

        $pathinfo = pathinfo($this->clientName);

        if (isset($pathinfo['extension'])) {
            return $pathinfo['extension'];
        }

        return explode('/', $this->clientMime)[1];
    }
}
