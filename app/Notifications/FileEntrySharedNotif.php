<?php

namespace App\Notifications;

use App\FileEntry;
use App\User;
use Common\Notifications\GetsUserPreferredChannels;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use NotificationChannels\Fcm\FcmMessage;

class FileEntrySharedNotif extends Notification implements ShouldQueue
{
    use Queueable, GetsUserPreferredChannels;

    public const NOTIF_ID = 'A01';

    public array|Collection $fileEntries;

    public function __construct(array $entryIds, public User $sharer)
    {
        $this->fileEntries = FileEntry::whereIn('id', $entryIds)->get();
    }

    public function toMail(User $notifiable): MailMessage
    {
        $message = (new MailMessage())
            ->subject(
                __('Files shared on :siteName', [
                    'siteName' => config('app.name'),
                ]),
            )
            ->line($this->getFirstLine());

        foreach ($this->getFileLines() as $line) {
            $message->line('- ' . $line['content']);
        }

        $message->action(__('View now'), url('drive/shares'));

        return $message;
    }

    public function toFcm($notifiable)
    {
        return FcmMessage::create()
            ->setData([
                'notifId' => self::NOTIF_ID,
                'multiple' =>
                    $this->fileEntries->count() > 1 ? 'true' : 'false',
                'entryId' => (string) $this->fileEntries->first()->id,
                'click_action' => 'FLUTTER_NOTIFICATION_CLICK',
            ])
            ->setNotification(
                \NotificationChannels\Fcm\Resources\Notification::create()
                    ->setTitle(rtrim($this->getFirstLine(), ':'))
                    ->setBody(
                        $this->fileEntries
                            ->slice(0, 5)
                            ->map->name->implode(', '),
                    ),
            );
    }

    public function toArray(User $notifiable): array
    {
        $data = [
            'mainAction' => [
                'action' => '',
            ],
            'lines' => [
                [
                    'content' => $this->getFirstLine(),
                ],
            ],
        ];

        $data['lines'] = array_merge($data['lines'], $this->getFileLines());

        return $data;
    }

    private function getFileLines(): array
    {
        $lines = [];

        foreach ($this->fileEntries as $fileEntry) {
            $lines[] = [
                'icon' => Str::kebab($fileEntry->type),
                'content' => $fileEntry->name,
                'action' => ['action' => '/drive/shares'],
            ];
        }

        return $lines;
    }

    private function getFirstLine(): string
    {
        $fileCount = $this->fileEntries->count();
        $username = $this->sharer->display_name;

        if ($this->fileEntries->count() === 1) {
            return __(':username shared a file with you', [
                'username' => $username,
            ]);
        } else {
            return __(':username has shared :count files with you', [
                'username' => $username,
                'count' => $fileCount,
            ]);
        }
    }
}
