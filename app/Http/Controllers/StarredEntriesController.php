<?php

namespace App\Http\Controllers;

use App\FileEntry;
use Common\Core\BaseController;
use Common\Tags\Tag;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class StarredEntriesController extends BaseController
{
    public const TAG_NAME = 'starred';

    public function __construct(private Request $request, private Tag $tag)
    {
    }

    public function add(): JsonResponse
    {
        $entryIds = $this->request->get('entryIds');

        $this->validate($this->request, [
            'entryIds' => 'required|array|exists:file_entries,id',
        ]);

        $this->authorize('update', [FileEntry::class, $entryIds]);

        $tag = $this->tag->where('name', self::TAG_NAME)->first();

        $tag->attachEntries($entryIds, $this->request->user()->id);

        return $this->success(['tag' => $tag]);
    }

    public function remove(): JsonResponse
    {
        $entryIds = $this->request->get('entryIds');

        $this->validate($this->request, [
            'entryIds' => 'required|array|exists:file_entries,id',
        ]);

        $this->authorize('update', [FileEntry::class, $entryIds]);

        $tag = $this->tag->where('name', self::TAG_NAME)->first();

        $tag->detachEntries($entryIds, $this->request->user()->id);

        return $this->success(['tag' => $tag]);
    }
}
