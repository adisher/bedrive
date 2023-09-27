<?php

namespace Common\Reports;

use Auth;
use Common\Core\BaseController;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Arr;

class ReportController extends BaseController
{
    public function store()
    {
        $data = $this->validate(request(), [
            'reason' => 'string:max:250',
            'model_type' => 'required|string',
            'model_id' => 'required|integer',
        ]);

        $namespace = modelTypeToNamespace($data['model_type']);
        $model = app($namespace)::findOrFail($data['model_id']);

        $this->authorize('show', $model);

        $userId = Auth::id();
        $userIp = getIp();

        // if we can't match current user, bail
        if (!$userId && !$userIp) {
            return null;
        }

        $alreadyReported = $model
            ->reports()
            ->where(
                fn(Builder $query) => $query
                    ->where('user_id', $userId)
                    ->orWhere('user_ip', $userIp),
            )
            ->first();

        if ($alreadyReported) {
            return $this->error(
                __('You have already submitted a report for this item.'),
            );
        } else {
            $model->reports()->create([
                'reason' => Arr::get($data, 'reason'),
                'user_id' => $userId,
                'user_ip' => $userIp,
            ]);
            return $this->success();
        }
    }

    public function destroy($modelType, $modelId)
    {
        $namespace = modelTypeToNamespace($modelType);
        $model = app($namespace)::findOrFail($modelId);

        $this->authorize('show', $model);

        $model
            ->reports()
            ->where(
                fn(Builder $query) => $query
                    ->where('user_id', Auth::id())
                    ->orWhere('user_ip', getIp()),
            )
            ->delete();

        return $this->success();
    }
}
