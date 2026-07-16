<?php

namespace Collector\Http\Controllers;

use Collector\Collector;
use Collector\Events\InvoiceCreated;
use Collector\Events\PaymentReceived;
use Collector\Events\SubscriptionCanceled;
use Collector\Events\WebhookReceived;
use Collector\Http\Middleware\VerifyWebhookSignature;
use Collector\Models\Subscription;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Illuminate\Http\JsonResponse;

class CollectorWebhookController extends Controller
{
    public function __construct()
    {
        if (config('collector.secret')) {
            $this->middleware(VerifyWebhookSignature::class);
        }
    }

    /**
     * @return JsonResponse
     */
    public function __invoke(Request $request)
    {
        $payload = json_decode($request->getContent(), true);

        $method = 'handle' . Str::studly(str_replace('.', '_', $payload['event']));

        WebhookReceived::dispatch($payload);

        if (method_exists($this, $method)) {
            $this->{$method}($payload);
        }

        return response()->json();
    }

    /**
     * @return void
     */
    protected function handleSubscriptionCreate(array $payload)
    {
        $user = Collector::findCollectable(data_get($payload, 'data.customer.email'));

        if (! $user) {
            return;
        }

        if (! $user->hasPayStackId()) {
            $user->forceFill([
                'paystack_id' => data_get($payload, 'data.customer.customer_code'),
            ])->save();
        }

        $planCode = data_get($payload, 'data.plan.plan_code');

        if (! $planCode || $user->hasActivePlan($planCode)) {
            return;
        }

        $paystackCustomer = $user->getAsPaystackCustomer();

        $paystackSubscription = $this->guessSubscription(
            $user,
            data_get($paystackCustomer, 'subscriptions', []),
            $planCode
        );

        if ($paystackSubscription) {
            /** @var Model $model */
            $model = new Subscription::$subscriptionModel();

            $model->fill([
                'name' => data_get($paystackSubscription, 'plan.name'),
                'user_id' => $user->id,
                'quantity' => 1,
                'paystack_email_token' => data_get($paystackSubscription, 'email_token'),
                'paystack_id' => data_get($paystackSubscription, 'subscription_code'),
                'paystack_status' => data_get($paystackSubscription, 'status'),
                'paystack_plan' => $planCode,
            ])->save();
        }
    }

    /**
     * @return void
     */
    protected function handleSubscriptionNotRenew(array $payload)
    {
        $customerId = data_get($payload, 'data.customer.customer_code');
        $subscriptionCode = data_get($payload, 'data.subscription_code');

        $user = Collector::findCollectable($customerId);

        if (! $user) {
            return;
        }

        $data = data_get($payload, 'data');
        $planCode = $data['plan']['plan_code'];

        if (! $user->hasActivePlan($planCode)) {
            return;
        }

        $subscription = Subscription::$subscriptionModel::where('paystack_id', $subscriptionCode)->first();

        SubscriptionCanceled::dispatch($user, $subscription);
    }

    /**
     * @return void
     */
    protected function handleChargeSuccess(array $payload)
    {
        $customerId = data_get($payload, 'data.customer.customer_code');
        $user = Collector::findCollectable($customerId);

        if (! $user) {
            return;
        }

        PaymentReceived::dispatch($user, $payload);
    }

    protected function handleInvoiceCreate(array $payload)
    {
        $customerId = data_get($payload, 'data.customer.customer_code');
        $user = Collector::findCollectable($customerId);

        if (! $user) {
            return;
        }

        InvoiceCreated::dispatch($user, $payload);
    }

    /**
     * @return void
     */
    protected function handleInvoicePaymentFailed(array $payload)
    {
        $customerId = data_get($payload, 'data.customer.customer_code');
        $user = Collector::findCollectable($customerId);

        if (! $user) {
            return;
        }

        PaymentReceived::dispatch($user, $payload);
    }

    /**
     * @return null
     */
    private function guessSubscription($collectable, array $subscriptions, $plan)
    {
        $ids = Arr::pluck($subscriptions, 'subscription_code');

        foreach ($ids as $id) {
            $subscription = $collectable->fetchSubscription($id);

            if ($subscription && data_get($subscription, 'plan.plan_code') === $plan) {
                return $subscription;
            }
        }

        return null;
    }
}
