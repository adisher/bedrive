<?php

namespace App\Services\Links;

use App\ShareableLink;
use Arr;
use Carbon\Carbon;
use Str;

class CrupdateShareableLink
{
    public function __construct(private ShareableLink $link)
    {
    }

    public function execute(array $params, ShareableLink $link = null)
    {
        if ($link !== null) {
            $link->fill($this->transformParams($params))->save();
        } else {
            $link = $this->link->create($this->transformParams($params));
        }

        return $link;
    }

    private function transformParams($params)
    {
        $transformed = [
            'user_id' => $params['userId'],
            'password' => $params['password'] ?? null,
            'allow_download' => $params['allowDownload'] ?? true,
            'allow_edit' => $params['allowEdit'] ?? false,
            'expires_at' => Arr::get($params, 'expiresAt')
                ? Carbon::parse($params['expiresAt'])
                : null,
        ];

        // creating a new link
        if (isset($params['entryId'])) {
            $transformed['entry_id'] = $params['entryId'];
            $transformed['hash'] = Str::random(30);
        }

        return $transformed;
    }
}
