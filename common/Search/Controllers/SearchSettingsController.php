<?php

namespace Common\Search\Controllers;

use Artisan;
use Common\Core\BaseController;
use Common\Search\ImportRecordsIntoScout;
use Str;

class SearchSettingsController extends BaseController
{
    public function getSearchableModels()
    {
        $models = ImportRecordsIntoScout::getSearchableModels();

        $models = array_map(function (string $model) {
            return [
                'model' => $model,
                'name' => Str::plural(last(explode('\\', $model))),
            ];
        }, $models);

        return $this->success(['models' => $models]);
    }

    public function import()
    {
        $this->middleware('isAdmin');

        (new ImportRecordsIntoScout())->execute(
            request('model'),
            request('driver'),
        );

        return $this->success(['output' => nl2br(Artisan::output())]);
    }
}
