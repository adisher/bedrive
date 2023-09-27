<?php

namespace App\Http\Controllers;

use App\FileEntry;
use Common\Core\BaseController;
use Common\Tags\Tag;
use Illuminate\Support\Facades\Auth;

class FileEntryTagsController extends BaseController
{
    public function index()
    {
        $this->authorize('index', Tag::class);

        $query = request('query');
        $model = app(Tag::class);
        if ($query) {
            $model = $model->search($query);
        }

        $results = $model
            ->take(15)
            ->get()
            ->where('type', 'custom')
            ->where('user_id', Auth::id())
            ->map(fn(Tag $model) => $model->toNormalizedArray())
            ->values();

        return $this->success(['results' => $results]);
    }

    public function sync(FileEntry $fileEntry)
    {
        $this->authorize('update', $fileEntry);

        $tagNames = request('tags', []);

        $tagIds = app(Tag::class)
            ->insertOrRetrieve($tagNames, 'custom', Auth::id())
            ->pluck('id');

        $fileEntry->tags()->sync(
            $tagIds->mapWithKeys(function ($id) {
                return [$id => ['user_id' => Auth::id()]];
            }),
        );

        return $this->success();
    }
}
