<?php

namespace Common\Pages;

use Illuminate\Support\Collection;

class LoadCustomPageMenuItems
{
    public function execute(): Collection
    {
        return app(CustomPage::class)
            ->limit(20)
            ->where('type', 'default')
            ->get()
            ->map(function (CustomPage $page) {
                return [
                    'id' => $page->id,
                    'label' => $page->title ?: $page->slug,
                    'action' => "/pages/{$page->slug}",
                    'model_id' => $page->id,
                    'type' => 'customPage',
                ];
            });
    }
}
