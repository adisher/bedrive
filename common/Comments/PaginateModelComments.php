<?php

namespace Common\Comments;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

class PaginateModelComments
{
    public function execute(Model $commentable): array
    {
        $pagination = $commentable
            ->comments()
            ->rootOnly()
            ->with([
                'user' => fn($builder) => $builder->compact(),
            ])
            ->paginate(request('perPage') ?? 25);

        $comments = app(LoadChildComments::class)->execute(
            $commentable,
            Collection::make($pagination->items()),
        );

        $comments->load([
            'votes' => fn($builder) => $builder->withCurrentUserVotes(),
        ]);

        $comments->transform(function (Comment $comment) {
            if ($comment->deleted) {
                $comment->content = null;
            }
            $comment->current_vote = $comment->votes->first()?->vote_type;
            $comment->unsetRelation('votes');
            return $comment;
        });

        $pagination = $pagination->toArray();
        $pagination['data'] = $comments;

        return $pagination;
    }
}
