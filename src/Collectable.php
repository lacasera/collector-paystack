<?php

namespace Collector;

use Collector\Models\Subscription;
use Collector\PayStack\ManagesCustomer;
use Collector\PayStack\ManagesPlans;
use Collector\PayStack\ManagesSubscription;
use Collector\PayStack\PrepareRequest;
use Illuminate\Http\Client\PendingRequest;

trait Collectable
{
    protected PendingRequest $request;

    use ManagesCustomer;
    use ManagesPlans;
    use ManagesSubscription;
    use PayStack;

    public function __construct()
    {
        parent::__construct();

        $this->request = PrepareRequest::prepare();
    }

    public static function bootCollectable()
    {
        static::created(function ($model) {
            $trialDays = 30;

            $model->forceFill([
                'trial_ends_at' => $trialDays ? now()->addDays($trialDays) : null,
            ])->save();
        });

        //        static::updated(function ($customer) {
        //            if ($customer->hasPayStackId() && $customer->shouldSyncCustomerDetailsToPayStack()) {
        //                //
        //            }
        //        });
    }

    public function collectorConfiguration($key = null)
    {
        $config = collect(config('collector.collectables'))
            ->map(function ($config, $type) {
                $config['type'] = $type;

                return $config;
            })->first(function ($billable, $type) {
                return $billable['model'] == get_class($this);
            });

        if ($key) {
            return $config[$key] ?? null;
        }

        return $config;
    }

    public function hasActivePlan(string $planId)
    {
        return Subscription::$subscriptionModel::where([
            'paystack_plan' => $planId,
            'paystack_status' => Subscription::ACTVIE_STATUS,
            'user_id' => $this->id,
        ])->first();
    }

    /**
     * @return mixed
     */
    public function currentActivePlan()
    {
        return Subscription::$subscriptionModel::where([
            'paystack_status' => Subscription::ACTVIE_STATUS,
            'user_id' => $this->id,
        ])->first();
    }
}
