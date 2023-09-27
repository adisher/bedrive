<?php

namespace Common\Votes;

use Common\Core\BaseController;

class VoteController extends BaseController
{
    public function store()
    {
        $data = $this->validate(request(), [
            'vote_type' => 'required|in:upvote,downvote',
            'model_type' => 'required|string',
            'model_id' => 'required|integer',
        ]);

        $namespace = modelTypeToNamespace($data['model_type']);
        $model = app($namespace)::findOrFail($data['model_id']);

        $this->authorize('vote', $model);

        $model = app(StoreVote::class)->execute(
            $model,
            $data['vote_type'],
            getIp(),
        );

        $model->unsetRelation('votes');
        $model->load([
            'votes' => fn($builder) => $builder->withCurrentUserVotes(),
        ]);
        $model->current_vote = $model->votes->first()?->vote_type;
        $model->unsetRelation('votes');

        return $this->success(['model' => $model]);
    }
}
