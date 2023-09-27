<?php

namespace Common\Files\S3;

use Aws\S3\S3Client;
use Common\Settings\Settings;
use Illuminate\Contracts\Filesystem\Filesystem;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

trait InteractsWithS3Api
{
    protected function getDiskName(): string
    {
        return request()->input('disk') ?: 'uploads';
    }

    protected function getDisk(): Filesystem
    {
        return Storage::disk($this->getDiskName());
    }

    protected function getClient(): ?S3Client
    {
        return $this->getDisk()->getClient();
    }

    protected function getBucket(): string
    {
        $credentialsKey = config(
            "common.site.{$this->getDiskName()}_disk_driver",
        );
        return config("services.{$credentialsKey}.bucket");
    }

    protected function getAcl(): string
    {
        return $this->getDiskName() === 'public' ||
            config('common.site.remote_file_visibility') === 'public'
            ? 'public-read'
            : 'private';
    }

    protected function buildFileKey(): string
    {
        $uuid = Str::uuid();
        $filename = request('filename');
        $extension = request('extension');
        $keepOriginalName = app(Settings::class)->get(
            'uploads.keep_original_name',
        );

        if ($this->getDiskName() === 'public') {
            $fileKey = $keepOriginalName ? $filename : "$uuid.${extension}";
            $diskPrefix = request('diskPrefix');
            if ($diskPrefix) {
                $fileKey = "$diskPrefix/$fileKey";
            }
        } else {
            $diskPrefix = $uuid;
            $filename = $keepOriginalName ? $filename : $uuid;
            $fileKey = "$diskPrefix/$filename";
        }

        $pathPrefix = $this->getDisk()->path('');

        if ($pathPrefix) {
            $fileKey = "{$pathPrefix}{$fileKey}";
        }

        return $fileKey;
    }
}
