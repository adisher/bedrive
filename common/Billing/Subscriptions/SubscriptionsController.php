<?php namespace Common\Billing\Subscriptions;

use Common\Billing\Models\Price;
use Common\Billing\Models\Product;
use Common\Billing\Subscription;
use Common\Core\BaseController;
use Common\Database\Datasource\Datasource;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class SubscriptionsController extends BaseController
{
    public function __construct(
        protected Request $request,
        protected Subscription $subscription
    ) {
        $this->middleware('auth');
    }

    public function index()
    {
        $this->authorize('index', Subscription::class);

        $dataSource = new Datasource(
            $this->subscription->with(['user']),
            $this->request->all(),
        );

        $pagination = $dataSource->paginate();

        return $this->success(['pagination' => $pagination]);
    }

    public function store()
    {
        $this->authorize('update', Subscription::class);

        $data = $this->validate($this->request, [
            'user_id' => 'required|exists:users,id|unique:subscriptions',
            'renews_at' => 'required_without:ends_at|date|nullable',
            'ends_at' => 'required_without:renews_at|date|nullable',
            'product_id' => 'required|integer|exists:products,id',
            'price_id' => 'required|integer|exists:prices,id',
            'description' => 'string|nullable',
        ]);

        $subscription = $this->subscription->create($data);

        return $this->success(['subscription' => $subscription]);
    }

    public function update(Subscription $subscription)
    {
        $this->authorize('update', Subscription::class);

        $data = $this->validate($this->request, [
            'user_id' => [
                'required',
                'exists:users,id',
                Rule::unique('subscriptions')->ignore($subscription->id),
            ],
            'renews_at' => 'date|nullable',
            'ends_at' => 'date|nullable',
            'product_id' => 'required|integer|exists:products,id',
            'price_id' => 'required|integer|exists:prices,id',
            'description' => 'string|nullable',
        ]);

        $subscription->fill($data)->save();

        return $this->success(['subscription' => $subscription]);
    }

    public function changePlan(Subscription $subscription)
    {
        $data = $this->validate($this->request, [
            'newProductId' => 'required|integer|exists:products,id',
            'newPriceId' => 'required|integer|exists:prices,id',
        ]);

        $newProduct = Product::findOrFail($data['newProductId']);
        $newPrice = Price::findOrFail($data['newPriceId']);

        $subscription->changePlan($newProduct, $newPrice);

        $user = $subscription->user()->first();
        return $this->success(['user' => $user->load('subscriptions.product')]);
    }

    public function cancel(Subscription $subscription)
    {
        $this->validate($this->request, [
            'delete' => 'boolean',
        ]);

        if ($this->request->get('delete')) {
            $subscription->cancelAndDelete();
        } else {
            $subscription->cancel();
        }

        return $this->success();
    }

    public function resume(Subscription $subscription)
    {
        $subscription->resume();
        return $this->success(['subscription' => $subscription]);
    }
}
