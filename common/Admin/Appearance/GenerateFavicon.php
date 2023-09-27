<?php

namespace Common\Admin\Appearance;

use Common\Settings\Settings;
use Illuminate\Support\Facades\File;
use Intervention\Image\Facades\Image;

class GenerateFavicon
{
    const FAVICON_DIR = 'favicon';
    protected string $absoluteFaviconDir;

    protected array $sizes = [
        [72, 72],
        [96, 96],
        [128, 128],
        [144, 144],
        [152, 152],
        [192, 192],
        [384, 384],
        [512, 512],
    ];
    protected string $initialFilePath;

    public function __construct()
    {
        $this->absoluteFaviconDir = public_path(self::FAVICON_DIR);
    }

    public function execute(string $filePath): void
    {
        if (!str_starts_with($filePath, 'http') && !file_exists($filePath)) {
            return;
        }

        File::ensureDirectoryExists($this->absoluteFaviconDir);
        $this->initialFilePath = $filePath;

        foreach ($this->sizes as $size) {
            $this->generateFaviconForSize($size);
        }
        $this->generateFaviconForSize([16, 16], public_path(), 'favicon.ico');

        $uri = self::FAVICON_DIR . '/icon-144x144.png';
        app(Settings::class)->save(['branding.favicon' => $uri]);
    }

    private function generateFaviconForSize(
        array $size,
        string $dir = null,
        string $name = null,
    ): void {
        $img = Image::make($this->initialFilePath);
        $img->fit($size[0], $size[1]);
        $img->encode('png');

        $dir = $dir ?? $this->absoluteFaviconDir;
        $name = $name ?? "icon-$size[0]x$size[1].png";

        File::put("$dir/$name", $img);
    }
}
