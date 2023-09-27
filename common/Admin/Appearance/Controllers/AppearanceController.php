<?php namespace Common\Admin\Appearance\Controllers;

use Common\Admin\Appearance\AppearanceSaver;
use Common\Admin\Appearance\AppearanceValues;
use Common\Core\BaseController;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AppearanceController extends BaseController
{
    public function __construct(
        protected Request $request,
        protected AppearanceValues $values,
        protected AppearanceSaver $saver,
    ) {
    }

    public function save()
    {
        $this->authorize('update', 'AppearancePolicy');

        $this->saver->save($this->request->get('changes'));
        return $this->success();
    }

    public function getValues(): JsonResponse
    {
        $this->authorize('update', 'AppearancePolicy');

        return $this->success($this->values->get());
    }
}
