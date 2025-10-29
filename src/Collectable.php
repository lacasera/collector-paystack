<?php

namespace Collector;

use Collector\Models\Subscription;
use Collector\PayStack\ManagesCustomer;
use Collector\PayStack\ManagesPlans;
use Collector\PayStack\ManagesSubscription;
use Collector\PayStack\PrepareRequest;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Client\PendingRequest;

trait Collectable
{
    use ManagesCustomer;
    use ManagesPlans;
    use ManagesSubscription;
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
        return Subscription::$subscriptionModel::where([
            'paystack_status' => Subscription::ACTIVE_STATUS,
            'user_id' => $this->id,
        ])->first();
    }
}
