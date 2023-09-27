<?php

namespace Common\Core\Prerender;

use Common\Core\Contracts\AppUrlGenerator;
use Common\Pages\CustomPage;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class BaseUrlGenerator implements AppUrlGenerator
{
    const SEPARATOR = '-';

    public function customPage(array|CustomPage $page): string
    {
        if (isset($page['page'])) {
            $originalSlug = $page['page']['slug'];
        } else {
            $originalSlug = $page['slug'];
        }

        $slug = slugify($originalSlug);
        return url("pages/$slug");
    }

    public function home(): string
    {
        return url('');
    }

    /**
     * @param Model|array $model
     */
    public function generate($model): string
    {
        $method =
            $model instanceof Model ? $model::MODEL_TYPE : $model['modelType'];
        return $this->$method($model);
    }

    public function __call(string $name, array $arguments): string
    {
        return url(Str::kebab($name));
    }
}
