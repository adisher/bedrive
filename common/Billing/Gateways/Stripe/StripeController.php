<?php namespace Common\Billing\Gateways\Stripe;

use Auth;
use Common\Billing\Models\Product;
use Common\Billing\Subscription;
use Common\Core\BaseController;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class StripeController extends BaseController
{
    public function __construct(
        protected Request $request,
        protected Subscription $subscription,
        protected Stripe $stripe
    ) {
        $this->middleware('auth');
    }

    public function createPartialSubscription(): Response|JsonResponse
    {
        $data = $this->validate($this->request, [
            'product_id' => 'required|integer|exists:products,id',
            'start_date' => 'string',
        ]);

        $product = Product::findOrFail($data['product_id']);
        $clientSecret = $this->stripe->subscriptions->createPartial(
            $product,
            Auth::user(),
        );

        return $this->success(['clientSecret' => $clientSecret]);
    }

    public function createSetupIntent(): Response|JsonResponse
    {
        $clientSecret = $this->stripe->createSetupIntent(Auth::user());
        return $this->success(['clientSecret' => $clientSecret]);
    }

    public function changeDefaultPaymentMethod(): Response|JsonResponse
    {
        $data = $this->validate($this->request, [
            'payment_method_id' => 'required|string',
        ]);

        $this->stripe->changeDefaultPaymentMethod(
            $this->request->user(),
            $data['payment_method_id'],
        );

        return $this->success();
    }

    public function storeSubscriptionDetailsLocally(): Response|JsonResponse
    {
        $data = $this->validate($this->request, [
            'payment_intent_id' => 'required|string',
        ]);

        $paymentIntent = $this->stripe->client->paymentIntents->retrieve(
            $data['payment_intent_id'],
            ['expand' => ['invoice']],
        );

        $this->stripe->storeSubscriptionDetailsLocally(
            $paymentIntent->invoice->subscription,
        );

        return $this->success();
    }
}
