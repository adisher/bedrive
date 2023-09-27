<?php namespace Common\Files\Response;

use Common\Files\FileEntry;
use League\Flysystem\AwsS3V3\AwsS3V3Adapter;
use League\Flysystem\Local\LocalFilesystemAdapter;
use Request;

class FileResponseFactory
{
    public function create(
        FileEntry $entry,
        string $disposition = 'inline'
    ): mixed {
        $options = [
            'useThumbnail' => Request::get('thumbnail') && $entry->thumbnail,
            'disposition' => $disposition,
        ];

        return $this->resolveResponseClass($entry, $disposition)->make(
            $entry,
            $options,
        );
    }

    private function resolveResponseClass(
        FileEntry $entry,
        string $disposition = 'inline'
    ): FileResponse {
        $isLocalDrive =
            $entry->getDisk()->getAdapter() instanceof LocalFilesystemAdapter;
        $staticFileDelivery = config('common.site.static_file_delivery');

        if ($this->shouldRedirectToRemoteUrl($entry)) {
            return new RemoteFileResponse();
        } elseif ($isLocalDrive && !$entry->public && $staticFileDelivery) {
            return $staticFileDelivery === 'xsendfile'
                ? new XSendFileResponse()
                : new XAccelRedirectFileResponse();
        } elseif (config('common.site.use_presigned_s3_urls')) {
            return new StreamedFileResponse();
        } elseif (
            $disposition === 'inline' &&
            $this->shouldReturnRangeResponse($entry)
        ) {
            return new RangeFileResponse();
        } else {
            return new StreamedFileResponse();
        }
    }

    private function shouldReturnRangeResponse(FileEntry $entry): bool
    {
        return $entry->type === 'video' ||
            $entry->type === 'audio' ||
            $entry->mime === 'application/ogg';
    }

    private function shouldRedirectToRemoteUrl(FileEntry $entry): bool
    {
        $adapter = $entry->getDisk()->getAdapter();
        $isS3 = $adapter instanceof AwsS3V3Adapter;
        $shouldUsePublicUrl =
            config('common.site.remote_file_visibility') === 'public' && $isS3;
        $shouldUsePresignedUrl =
            config('common.site.use_presigned_s3_urls') && $isS3;
        return $shouldUsePresignedUrl || $shouldUsePublicUrl;
    }
}
