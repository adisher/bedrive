<?php

namespace Common\Billing\Invoices;

use Common\Core\AppUrl;
use Common\Core\BaseController;
use Common\Settings\Settings;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class InvoiceController extends BaseController
{
    public function __construct(
        protected Request $request,
        protected Invoice $invoice
    ) {
    }

    public function index(): Response|JsonResponse
    {
        $this->authorize('index', [
            Invoice::class,
            $this->request->get('userId'),
        ]);

        $invoices = $this->invoice
            ->with('subscription.product', 'subscription.price')
            ->whereHas('subscription', function (Builder $builder) {
                $builder->where('user_id', $this->request->get('userId'));
            })
            ->limit(50)
            ->get();

        return $this->success(['invoices' => $invoices]);
    }

    public function show(string $uuid)
    {
        $invoice = $this->invoice
            ->where('uuid', $uuid)
            ->with(
                'subscription.product',
                'subscription.user',
                'subscription.price',
            )
            ->firstOrFail();

        $this->authorize('show', $invoice);

        return view('common::billing/invoice')
            ->with('invoice', $invoice)
            ->with('htmlBaseUri', app(AppUrl::class)->htmlBaseUri)
            ->with('user', $invoice->subscription->user)
            ->with('settings', app(Settings::class));
    }
}
