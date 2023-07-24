<?php

namespace Collector;

use Collector\PayStack\ManagesCustomer;
use Collector\PayStack\ManagesPlans;
use Collector\PayStack\ManagesSubscription;

trait Collectable
{
    use PayStack;
    use ManagesPlans;
    use ManagesCustomer;
    use ManagesSubscription;


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
}
