<?php

namespace Common\Files\Traits;

use Str;

trait GetsEntryTypeFromMime
{
    protected function getTypeFromMime(string $mime, string $extension = null): string
    {
        $default = explode('/', $mime)[0];

        if ($mime === 'text/plain' && $extension === 'csv') {
            return 'spreadsheet';
        }

        switch ($mime) {
            case 'application/x-zip-compressed':
            case 'application/zip':
                return 'archive';
            case 'application/pdf':
                return 'pdf';
            case 'image/svg':
                return 'image/svg+xml';
            case 'image/vnd.dwg':
                return 'file';
            case 'vnd.android.package-archive':
                return 'android package';
            case Str::contains($mime, ['xls', 'excel', 'spreadsheetml']):
                return 'spreadsheet';
            case Str::contains($mime, 'photoshop'):
                return 'photoshop';
            case Str::contains($mime, 'officedocument.presentation'):
                return 'powerPoint';
            case Str::contains($mime, [
                'application/msword',
                'wordprocessingml.document',
            ]):
                return 'word';
            case Str::contains($mime, ['postscript', 'x-eps']):
                return 'postscript';
            case Str::startsWith($mime, 'message/rfc'):
                return 'text/plain';
            default:
                return $default === 'application' ? 'file' : $default;
        }
    }
}
