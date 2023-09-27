<?php

namespace Common\Notifications;

use Common\Settings\Settings;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ContactPageMessage extends Notification
{
    use Queueable;

    public function __construct(public $message)
    {
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return MailMessage
     */
    public function toMail($notifiable)
    {
        $siteName = app(Settings::class)->get('branding.site_name');
        $userEmail = $this->message['email'];

        return (new MailMessage())
            ->subject(
                __('New message via :siteName contact page.', [
                    'siteName' => $siteName,
                ]),
            )
            ->greeting(
                __("New message via :siteName contact page from ':userEmail'", [
                    'siteName' => $siteName,
                    'userEmail' => $userEmail,
                ]),
            )
            ->salutation(' ')
            ->from(config('mail.from.address'), config('mail.from.name'))
            ->replyTo($userEmail, $this->message['name'])
            ->line($this->message['message']);
    }

    /**
     * Get the array representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function toArray($notifiable)
    {
        return [
                //
            ];
    }
}
