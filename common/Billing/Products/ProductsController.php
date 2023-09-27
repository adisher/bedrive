<?php namespace Common\Billing\Products;

use Common\Billing\Gateways\Paypal\Paypal;
use Common\Billing\Gateways\Stripe\Stripe;
use Common\Billing\Models\Product;
use Common\Billing\Products\Actions\CrupdateProduct;
use Common\Core\BaseController;
use Common\Database\Datasource\Datasource;
use Exception;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Illuminate\Validation\Rule;

class ProductsController extends BaseController
{
    public function __construct(
        protected Stripe $stripe,
        protected Paypal $paypal
    ) {
    }

    public function index()
    {
        $this->authorize('index', Product::class);

        $dataSource = new Datasource(
            Product::with(['permissions', 'prices']),
            request()->all(),
        );
        $dataSource->order = ['col' => 'position', 'dir' => 'asc'];

        return $this->success(['pagination' => $dataSource->paginate()]);
    }

    public function show(Product $product)
    {
        $this->authorize('show', $product);

        $product->load([
            'permissions',
            'prices' => fn(HasMany $builder) => $builder->withCount(
                'subscriptions',
            ),
        ]);

        return ['product' => $product];
    }

    public function store()
    {
        $this->authorize('store', Product::class);

        $this->validate(request(), [
            'name' => 'required|string|max:250',
            'permissions' => 'array',
            'recommended' => 'boolean',
            'position' => 'integer',
            'available_space' => 'nullable|integer|min:1',
            'prices' => ['array', Rule::requiredIf(!request('free'))],
            'prices.*.currency' => 'required|string|max:255',
            'prices.*.interval' => 'string|max:255',
            'prices.*.amount' => 'min:1',
        ]);

        $plan = app(CrupdateProduct::class)->execute(request()->all());

        return $this->success(['plan' => $plan]);
    }

    public function update(Product $product)
    {
        $this->authorize('update', $product);

        $this->validate(request(), [
            'name' => 'required|string|max:250',
            'permissions' => 'array',
            'recommended' => 'boolean',
            'prices' => ['array', Rule::requiredIf(!request('free'))],
            'prices.*.currency' => 'required|string|max:255',
            'prices.*.interval' => 'string|max:255',
            'prices.*.amount' => 'min:1',
        ]);

        $product = app(CrupdateProduct::class)->execute(
            request()->all(),
            $product,
        );

        return $this->success(['product' => $product]);
    }

    public function destroy(Product $product): Response|JsonResponse
    {
        $this->authorize('destroy', $product);

        if ($product->subscriptions_count) {
            return $this->error(
                __(
                    "Could not delete ':plan', because it has active subscriptions.",
                    ['plan' => $product->name],
                ),
            );
        }

        try {
            if ($this->stripe->isEnabled()) {
                $this->stripe->deletePlan($product);
            }
            if ($this->paypal->isEnabled()) {
                $this->paypal->deletePlan($product);
            }
        } catch (Exception $e) {
            return $this->error($e->getMessage());
        }

        $product->delete();

        return $this->success();
    }
}
