<?php

namespace Common\Settings\Validators;

use Common\Files\Actions\CreateFileEntry;
use Common\Files\Actions\Deletion\PermanentlyDeleteEntries;
use Common\Files\Actions\StoreFile;
use Common\Files\FileEntryPayload;
use Common\Settings\DotEnvEditor;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class StaticFileDeliveryValidator implements SettingsValidator
{
    const KEYS = ['static_file_delivery'];

    public function fails($settings): bool|array
    {
        if (!$settings['static_file_delivery']) {
            return false;
        }

        $originalDelivery = config('common.site.static_file_delivery');
        $originalDriver = config('common.site.uploads_disk_driver');

        app(DotEnvEditor::class)->write([
            'STATIC_FILE_DELIVERY' => $settings['static_file_delivery'],
            'UPLOADS_DISK_DRIVER' => 'local',
        ]);

        $previewToken = Str::random(10);
        $contents = Str::random(10);

        $path = base_path('common/resources/lorem.html');
        $uploadedFile = new UploadedFile(
            $path,
            basename($path),
            'text/html',
            filesize($path),
        );
        $payload = new FileEntryPayload([
            'file' => $uploadedFile,
        ]);
        $fileEntry = app(CreateFileEntry::class)->execute($payload);
        $fileEntry->fill(['preview_token' => $previewToken])->save();
        app(StoreFile::class)->execute($payload, ['file' => $uploadedFile]);

        $response = Http::get(
            url($fileEntry->url) . "?preview_token=$previewToken",
        );
        app(PermanentlyDeleteEntries::class)->execute([$fileEntry->id]);

        app(DotEnvEditor::class)->write([
            'STATIC_FILE_DELIVERY' => $originalDelivery,
            'UPLOADS_DISK_DRIVER' => $originalDriver,
        ]);

        if ($contents !== $response->body()) {
            return [
                'static_delivery_group' => __(
                    'Could not validate selected optimization. Is it enabled on the server?',
                ),
            ];
        } else {
            return false;
        }
    }
}
