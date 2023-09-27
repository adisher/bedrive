<?php

namespace Common\Comments\Notifications;

use App\Services\UrlGenerator;
use App\User;
use Common\Comments\Comment;
use Illuminate\Bus\Queueable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Str;

class CommentReceivedReply extends Notification
{
    use Queueable;

    public Model $commentable;

    public function __construct(
        public Comment $newComment,
        public array $originalComment
    ) {
        $this->newComment = $newComment;
        $this->originalComment = $originalComment;
        $this->commentable = app(
            modelTypeToNamespace($newComment['commentable_type']),
        )->find($newComment['commentable_id']);
    }

    public function via(User $notifiable): array
    {
        return ['database'];
    }

    public function toArray(User $notifiable): array
    {
        $username = $this->newComment['user']['display_name'];
        $commentable = $this->commentable->toNormalizedArray();

        return [
            'image' => $this->originalComment['user']['avatar'],
            'mainAction' => [
                'action' => app(UrlGenerator::class)->generate(
                    $this->commentable,
                ),
            ],
            'lines' => [
                [
                    'content' => __(':username replied to your comment:', [
                        'username' => $username,
                    ]),
                    'action' => [
                        'action' => app(UrlGenerator::class)->user(
                            $this->newComment['user'],
                        ),
                        'label' => __('View user'),
                    ],
                    'type' => 'secondary',
                ],
                [
                    'content' =>
                        '"' .
                        Str::limit($this->newComment['content'], 180) .
                        '"',
                    'icon' => 'comment',
                    'type' => 'primary',
                ],
                [
                    'content' => __('on') . " {$commentable['name']}",
                    'type' => 'secondary',
                ],
            ],
        ];
    }
}
