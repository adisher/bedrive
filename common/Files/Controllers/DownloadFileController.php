<?php namespace Common\Files\Controllers;

use Common\Core\BaseController;
use Common\Files\FileEntry;
use Common\Files\Response\DownloadFilesResponse;
use Common\Files\Response\FileResponseFactory;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class DownloadFileController extends BaseController
{
    public function __construct(
        protected Request $request,
        protected FileEntry $fileEntry,
        protected FileResponseFactory $fileResponseFactory,
    ) {
    }

    public function download(string $hashes)
    {
        $hashes = explode(',', $hashes);
        $ids = array_map(function ($hash) {
            return $this->fileEntry->decodeHash($hash);
        }, $hashes);
        $ids = array_filter($ids);

        if (!$ids) {
            abort(404, 'No entry hashes provided.');
        }

        $entries = $this->fileEntry->whereIn('id', $ids)->get();

        $this->authorize('download', [FileEntry::class, $entries]);

        return app(DownloadFilesResponse::class)->create($entries);
    }
}
