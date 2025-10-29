<?php

namespace Collector;

use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use InvalidArgumentException;

class CollectorManager
{
    private $collectableResolvingCallbacks = [];

    private $authorizationCallbacks = [];

    public $plans = [];

    /**
     * The default customer model class name.
     *
     * @var string
     */
    protected static $customerModel = 'App\\Models\\User';

    public function collectable(string $class)
    {
        foreach (config('collector.collectables') as $type => $config) {
            if (Arr::get($config, 'model') == $class) {
                return new CollectableConfigBuilder($type);
            }
        }

        throw new InvalidArgumentException("No collectable has been defined for the [{$class}] model.");
    }

    public function resolveCollectableUsing($type, $callback)
    {
        $this->collectableResolvingCallbacks[$type] = $callback;
    }

    public function resolveCollectable($type, Request $request)
    {
        if (isset($this->collectableResolvingCallbacks[$type])) {
            return call_user_func($this->collectableResolvingCallbacks[$type], $request);
        }
    }

    public function collectableModel($collectableType)
    {
        return config("collector.collectables.$collectableType.model");
    }

    /**
     * @return mixed
     */
    public static function findCollectable(string $paystackId)
    {
        return (new static::$customerModel())
            ->where('paystack_id', $paystackId)
            ->orWhere('email', $paystackId)
            ->first();
    }

    /**
     * Set the customer model class name.
     *
     * @param  string  $customerModel
     * @return void
     */
    public static function useCustomerModel($customerModel)
    {
        static::$customerModel = $customerModel;
    }

    public function isAuthorizedToViewBillingPortal($billable, Request $request)
    {
        $type = $billable->collectorConfiguration('type');

        if ($callback = $this->authorizationCallbacks[$type] ?? null) {
            if (! call_user_func($callback, $billable, $request)) {
                return false;
            }
        }

        return true;
    }

    public function authorizeUsing($type, $callback)
    {
        $this->authorizationCallbacks[$type] = $callback;
    }

    public function plans($collectableType)
    {
        if (isset($this->plans[$collectableType])) {
            return collect($this->plans[$collectableType]);
        }

        return $this->plans[$collectableType] = $this->toPlans(
            config("collector.collectables.$collectableType.plans")
        );
    }

    protected function toPlans(array $config)
    {
        $plans = collect();

        foreach (collect($config) as $plan) {
            foreach (['monthly', 'yearly', 'hourly', 'daily'] as $interval) {
                if (! $id = $plan[$interval . '_id'] ?? null) {
                    continue;
                }

                $plans->push(
                    (new Plan($plan['name'], $id))
                        ->interval($interval)
                        ->incentive($plan['monthly_incentive'] ?? '', $plan['yearly_incentive'] ?? '')
                        ->description($plan['description'])
                        ->features($plan['features'])
                        ->options($plan['options'] ?? [])
                        ->trialDays($plan['trial_days'] ?? null)
                        ->status(isset($plan['archived']) ? ! $plan['archived'] : true)
                );
            }
        }

        return $plans;
    }
}
