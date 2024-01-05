<?php

namespace Collector\PayStack;

use Carbon\Carbon;
use Collector\Models\Subscription;
use Collector\Plan;
use Collector\SubscriptionBuilder;
use Illuminate\Database\Eloquent\Relations\HasMany;

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
     * @return array|mixed
     */
    public function transctionHistroy()
    {
        if (!$this->hasPayStackId()) {
            return [];
        }

        $results = collect([]);

        $page = 1;
        $transactions = $this->fetchTransactions();
        $pageCount = (int) data_get($transactions, 'meta.pageCount');
        $results = $results->merge(data_get($transactions, 'data'));

        while ($page < $pageCount) {
            $page = $page + 1;
            $transactions = $this->fetchTransactions($page);
            $results = $results->merge(data_get($transactions, 'data') ?? []);
        }

        return $results
            ->map(function ($transaction) {
                $amount = $transaction['amount'] / 100;
                return [
                    'price' => $amount,
                    'formatted_price' => $transaction['currency'] . $amount,
                    'currency' => $transaction['currency'],
                    'created_at' => Carbon::parse($transaction['created_at'])->format('jS F, Y'),
                    'status' => $transaction['status'] === 'success' ? 'Paid' : 'Overdue',
                    'payment' => [
                        'channel' => data_get($transaction, 'authorization.channel'),
                        'country_code' => data_get($transaction, 'authorization.country_code'),
                        'brand' => data_get($transaction, 'authorization.brand'),
                        'card_type' => data_get($transaction, 'authorization.card_type'),
                        'exp_month' => data_get($transaction, 'authorization.exp_month'),
                        'exp_year' => data_get($transaction, 'authorization.exp_year'),
                        'last4' => data_get($transaction, 'authorization.last4'),
                    ]
                ];
            });
    }

    private function fetchTransactions($page = 1)
    {
        $response = $this->request->get('/transaction', [
            'customer_id' => $this->paystack_id,
            'from' => Carbon::parse()->startOfYear()->toDateTimeString(),
            'to' => Carbon::parse()->endOfYear()->toDateTimeString(),
            'status' => 'success',
            'perPage' => 50,
            'page' => $page
        ]);

        if (!$response->ok()) {
            return [];
        }

        return $response->json();
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
     * @return HasMany
     */
    public function subscriptions(): HasMany
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
