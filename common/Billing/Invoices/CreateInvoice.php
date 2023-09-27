<?php

namespace Common\Billing\Invoices;

use Common\Billing\Notifications\NewInvoiceAvailable;
use Common\Billing\Subscription;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;

class CreateInvoice
{
    public function execute(array $data): Invoice
    {
        $invoice = new Invoice([
            'subscription_id' => $data['subscription_id'],
            'paid' => $data['paid'],
            'uuid' => Str::random(10),
            'notes' => Arr::get($data, 'notes'),
        ]);

        $invoice->save();

        Subscription::find($data['subscription_id'])->user->notify(
            new NewInvoiceAvailable($invoice),
        );

        return $invoice;
    }
}
