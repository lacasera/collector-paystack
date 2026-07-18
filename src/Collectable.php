<?php

namespace Collector;

use Collector\Models\Subscription;
use Collector\PayStack\ManagesCustomer;
use Collector\PayStack\ManagesPlans;
use Collector\PayStack\ManagesSubscription;
use Collector\PayStack\ManagesTransactions;
use Collector\PayStack\PrepareRequest;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Client\PendingRequest;

trait Collectable
{
    use ManagesCustomer;
    use ManagesPlans;
    use ManagesSubscription;
    use ManagesTransactions;
    use PayStack;

    protected PendingRequest $request;

    public function initializeCollectable(): void
    {
        $this->request = PrepareRequest::prepare();
    }

    public static function bootCollectable(): void
    {
        static::created(function (Model $model) {
            $trialDays = $model->collectorConfiguration('trial_days') ?? 30;

            $model->forceFill([
                'trial_ends_at' => $trialDays ? now()->addDays($trialDays) : null,
            ])->save();
        });
    }

    public function collectorConfiguration(?string $key = null): mixed
    {
        $config = collect(config('collector.collectables'))
            ->map(function (array $config, string $type): array {
                $config['type'] = $type;

                return $config;
            })->first(function (array $billable): bool {
                return $billable['model'] === static::class;
            });

        if ($key !== null) {
            return $config[$key] ?? null;
        }

        return $config;
    }

    public function hasActivePlan(string $planId): ?Subscription
    {
        return Subscription::$subscriptionModel::where([
            'paystack_plan' => $planId,
            'paystack_status' => Subscription::ACTIVE_STATUS,
            'user_id' => $this->id,
        ])->first();
    }

    public function currentActivePlan(): ?Subscription
    {
        // Ordered so the result is stable: an unordered first() returns an
        // arbitrary row whenever more than one subscription is active, which
        // makes the portal highlight an unpredictable plan.
        return Subscription::$subscriptionModel::where([
            'paystack_status' => Subscription::ACTIVE_STATUS,
            'user_id' => $this->id,
        ])->latest('id')->first();
    }

    /**
     * The subscription the billing portal should display.
     *
     * Broader than currentActivePlan(): a cancelled subscription still inside
     * its grace period is no longer "active", but the customer keeps access
     * until it ends and needs to see that — otherwise cancelling appears to
     * erase the subscription immediately.
     */
    public function currentSubscription(): ?Subscription
    {
        // An active subscription always wins. Falling back only when there is
        // none avoids an older cancelled-but-not-yet-expired subscription
        // shadowing the plan the customer is actually paying for.
        return $this->currentActivePlan() ?? Subscription::$subscriptionModel::where('user_id', $this->id)
            ->whereNotNull('ends_at')
            ->where('ends_at', '>', now())
            ->latest('id')
            ->first();
    }

    /**
     * Get the URL of the billing portal for this model.
     *
     * PayStack has no hosted billing portal, so this returns the package's own
     * portal route. The optional $return argument is accepted for Cashier API
     * parity (the portal is in-app, so there is no external return URL).
     */
    public function billingPortalUrl(?string $return = null): string
    {
        return route('collector.portal');
    }
}
