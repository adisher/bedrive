<?php

namespace Common\Comments;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

class LoadChildComments
{
    public function execute(
        Model $commentable,
        Collection $rootComments,
    ): Collection {
        $paths = $rootComments
            ->map(function (Comment $comment) {
                $path = $comment->getRawOriginal('path');
                return "LIKE '$path%'";
            })
            ->implode(' OR path ');

        $childComments = app(Comment::class)
            ->with(['user' => fn($builder) => $builder->compact()])
            ->where('commentable_id', $commentable->id)
            ->where('commentable_type', get_class($commentable))
            ->childrenOnly()
            ->where(fn(Builder $builder) => $builder->whereRaw("path $paths"))
            ->orderBy('path', 'asc')
            ->orderBy('created_at', 'desc')
            ->orderByWeightedScore()
            ->limit(100)
            ->get();

        $childComments->each(function ($child) use ($rootComments) {
            $index = $rootComments->search(
                fn($parent) => $parent['id'] === $child['parent_id'],
            );
            $rootComments->splice($index + 1, 0, [$child]);
        });

        return $rootComments;
    }
}
