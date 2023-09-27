<?php

namespace Common\Comments\Controllers;

use Common\Comments\PaginateModelComments;
use Common\Core\BaseController;

class CommentableController extends BaseController
{
    public function index()
    {
        $modelType = request('commentable_type');
        $modelId = request('commentable_id');

        if (!$modelType || !$modelId) {
            abort(404);
        }

        $commentable = app(modelTypeToNamespace($modelType))->findOrFail(
            $modelId,
        );

        $pagination = app(PaginateModelComments::class)->execute($commentable);

        return $this->success([
            'pagination' => $pagination,
        ]);
    }
}
