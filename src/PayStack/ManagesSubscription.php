<?php

namespace Collector\PayStack;

use Collector\Models\Subscription;
use Collector\Plan;
use Collector\SubscriptionBuilder;

trait ManagesSubscription
{
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
     * @return SubscriptionBuilder
     */
    public function newSubscription(Plan $plan, $prices = [])
    {
        return new SubscriptionBuilder($this, $plan->name, $prices);
    }

    /**
     * @return string|null
     */
    public function initiateTransaction($customer, $plan)
    {
        $response = $this->request->post('/transaction/initialize', [
            'email' => $customer->email,
            'plan' => $plan,
            'callback_url' => route('collector.portal'),
            'amount' => 1000, // plan option will override this amount :)
        ]);

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

        if (! data_get($response->json('data'), 'status') === 'success') {
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
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
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
