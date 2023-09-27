<?php

namespace Common\Pages;

use Common\Workspaces\ActiveWorkspace;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;

class CrupdatePage
{
    public function execute(CustomPage $page, array $data): CustomPage
    {
        if (!$page->exists) {
            $data['user_id'] = Auth::id();
            $data['slug'] = $data['slug'] ?? slugify(Arr::get($data, 'title'));
            $data['workspace_id'] = app(ActiveWorkspace::class)->id ?? 0;
        }

        $page->fill($data)->save();

        return $page;
    }
}
