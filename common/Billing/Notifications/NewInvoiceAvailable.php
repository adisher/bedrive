<?php

namespace Common\Billing\Notifications;

use App\User;
use Common\Billing\Invoices\Invoice;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class NewInvoiceAvailable extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public Invoice $invoice)
    {
    }

    public function via(mixed $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(User $notifiable): MailMessage
    {
        return (new MailMessage())
            ->subject($this->mainLine())
            ->level('error')
            ->greeting(
                __('Hello, :name', ['name' => $notifiable->display_name]),
            )
            ->line($this->descriptionLine())
            ->action(__('View receipt'), $this->mainAction());
    }

    public function toArray(mixed $notifiable): array
    {
        return [
            'lines' => [
                [
                    'content' => $this->mainLine(),
                ],
                [
                    'content' => $this->descriptionLine(),
                ],
            ],
            'buttonActions' => [
                [
                    'label' => __('View receipt'),
                    'action' => $this->mainAction(),
                ],
            ],
        ];
    }

    protected function mainLine(): string
    {
        $siteName = config('app.name');
        return __(':name payment receipt', [
            'name' => $siteName,
        ]);
    }

    protected function descriptionLine(): string
    {
        $siteName = config('app.name');
        return __('This is a receipt for your latest :siteName payment.', [
            'siteName' => $siteName,
        ]);
    }

    protected function mainAction(): string
    {
        return config('app.url') . '/billing/invoices/' . $this->invoice->uuid;
    }
}
