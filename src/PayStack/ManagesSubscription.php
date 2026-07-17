<?php

namespace Collector\PayStack;

use Collector\Collector;
use Collector\Models\Subscription;
use Collector\SubscriptionBuilder;
use Illuminate\Database\Eloquent\Relations\HasMany;

trait ManagesSubscription
{
    /**
     * Begin creating a new subscription (Cashier-style fluent builder).
     */
    public function newSubscription(string $name, string $plan): SubscriptionBuilder
    {
        return new SubscriptionBuilder($this, $name, $plan);
    }

    /**
     * Determine if the model has an active subscription (optionally by name).
     */
    public function subscribed(?string $name = null): bool
    {
        return ! is_null($this->subscription($name));
    }

    /**
     * Determine if the model is actively subscribed to a given PayStack plan.
     *
     * The PayStack equivalent of Cashier's subscribedToPrice().
     */
    public function subscribedToPlan(string $plan): bool
    {
        return ! is_null($this->hasActivePlan($plan));
    }

    /**
     * Determine if the model is actively subscribed to any plan configured
     * under the given product (the `name` group in config/collector.php).
     *
     * The PayStack equivalent of Cashier's subscribedToProduct().
     */
    public function subscribedToProduct(string $product): bool
    {
        $planCodes = Collector::plans($this->collectorConfiguration('type'))
            ->where('name', $product)
            ->pluck('id')
            ->all();

        return $this->subscriptions
            ->whereIn('paystack_plan', $planCodes)
            ->where('paystack_status', Subscription::ACTIVE_STATUS)
            ->isNotEmpty();
    }

    /**
     * @return array|mixed|null
     */
    public function fetchSubscription(string $code)
    {
        $response = $this->request->get("/subscription/$code");

        if (! $response->ok()) {
            return null;
        }

        return $response->json('data');
    }

    /**
     * @return string|null
     */
    public function initiateTransaction($customer, $plan, array $options = [])
    {
        $response = $this->request->post('/transaction/initialize', array_merge([
            'email' => $customer->email,
            'plan' => $plan,
            'callback_url' => route('collector.portal'),
            'amount' => 1000, // plan option will override this amount :)
        ], $options));

        if (! $response->ok()) {
            return null;
        }

        return data_get($response->json('data'), 'authorization_url');
    }

    /**
     * @return array|null
     */
    public function completedTransaction(string $reference)
    {
        $response = $this->request->get("/transaction/verify/$reference");

        if (! $response->ok()) {
            return null;
        }

        if (data_get($response->json('data'), 'status') !== 'success') {
            return null;
        }

        return $response->json('data');
    }

    public function cancelOnPayStack($subscription)
    {
        $response = $this->request->post('subscription/disable', [
            'code' => $subscription->paystack_id,
            'token' => $subscription->paystack_email_token,
        ]);

        if (! $response->ok()) {
            return null;
        }

        return $response->json('data');
    }

    /**
     * @return HasMany
     */
    public function subscriptions()
    {
        return $this->hasMany(Subscription::$subscriptionModel, $this->getForeignKey())
            ->orderBy('created_at', 'desc');
    }

    /**
     * @return mixed
     */
    public function subscription($name = null)
    {
        if ($name) {
            return $this->subscriptions->where('name', $name)
                ->where('paystack_status', Subscription::ACTIVE_STATUS)
                ->first();
        }

        return $this->subscriptions->where('paystack_status', Subscription::ACTIVE_STATUS)->first();
    }
}
