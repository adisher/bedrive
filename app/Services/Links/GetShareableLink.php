<?php

namespace App\Services\Links;

use App\FileEntry;
use App\Services\Entries\SetPermissionsOnEntry;
use App\ShareableLink;
use Common\Database\Paginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;

class GetShareableLink
{
    public function __construct(
        private ShareableLink $link,
        private FileEntry $entry,
    ) {
    }

    public function execute(int|string $idOrHash, array $params = []): array
    {
        $response = [];
        if (is_int($idOrHash) || ctype_digit($idOrHash)) {
            $response['link'] = $this->getByEntryId($idOrHash);
        } else {
            $parts = explode(':', $idOrHash);
            $response['link'] = $this->getByHash(
                $parts[0],
                Arr::get($parts, 1),
            );
        }

        if (
            Arr::get($params, 'withEntries') &&
            $response['link'] &&
            $response['link']->entry
        ) {
            $response['link']->entry->load('users');
            $response['link']->entry = app(
                SetPermissionsOnEntry::class,
            )->execute($response['link']->entry);
            if ($response['link']->entry->type === 'folder') {
                $response['folderChildren'] = $this->getChildrenFor(
                    $response['link'],
                    $params,
                );
            }
        }

        return $response;
    }

    private function getChildrenFor(
        ShareableLink $link,
        array $params,
    ): LengthAwarePaginator {
        $params['perPage'] = 50;
        $paginator = new Paginator($this->entry, $params);
        $paginator->setDefaultOrderColumns('updated_at', 'desc');

        // folders should always be first
        $paginator->query()->orderBy(DB::raw('type = "folder"'), 'desc');

        // avoid duplicates, if updated_at columns are equal
        $paginator->secondaryOrderCallback = function (Builder $query) {
            $query->orderBy('id', 'desc');
        };

        return $paginator
            ->with('users')
            ->where('parent_id', $link->entry->id)
            ->paginate()
            ->through(
                fn(FileEntry $entry) => app(
                    SetPermissionsOnEntry::class,
                )->execute($entry),
            );
    }

    private function getByHash($hash, $folderHash): ShareableLink
    {
        $link = $this->link->where('hash', $hash)->firstOrFail();

        // load sub folder for main link entry, if folderHash provided
        if ($folderHash) {
            $link->setRelation(
                'entry',
                $this->entry->whereHash($folderHash)->first(),
            );
        }

        return $link;
    }

    private function getByEntryId(int $entryId): ShareableLink|null
    {
        return $this->link->where('entry_id', $entryId)->first();
    }
}
