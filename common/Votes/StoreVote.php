<?php

namespace Common\Votes;

use Auth;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;

class StoreVote
{
    public function execute($model, string $newVoteType, ?string $userIp)
    {
        $userId = Auth::id();

        // if we can't match current user, bail
        if (!$userId && !$userIp) {
            return null;
        }

        $currentVote = $model
            ->votes()
            ->where(
                fn(Builder $query) => $query
                    ->where('user_id', $userId)
                    ->orWhere('user_ip', $userIp),
            )
            ->first();

        // remove old vote from this user
        if ($currentVote) {
            $this->deleteCurrentVote($model, $currentVote);
        }

        // create a new rating
        if (!$currentVote || $currentVote->vote_type !== $newVoteType) {
            $this->storeVote($model, $newVoteType, $userId, $userIp);
        }

        return $model;
    }

    private function deleteCurrentVote($model, Vote $vote): void
    {
        $column = Str::plural($vote->vote_type);
        if ($model->$column > 0) {
            $model->decrement($column);
        }
        $vote->delete();
    }

    private function storeVote(
        $model,
        string $voteType,
        ?int $userId,
        ?string $userIp,
    ): void
    {
        $model->votes()->create([
            'vote_type' => $voteType,
            'user_id' => $userId,
            'user_ip' => $userIp,
        ]);

        $model->increment(Str::plural($voteType), 1);
    }
}
