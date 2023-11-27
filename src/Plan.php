<?php

namespace Collector;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Traits\Macroable;
use JsonSerializable;

class Plan implements Arrayable, JsonSerializable
{
    use Macroable;

    /**
     * The plan's Paystack ID.
     *
     * @var string
     */
    public $id;

    /**
     * The plan's displayable name.
     *
     * @var string
     */
    public $name;

    /**
     * The plan's interval.
     *
     * @var string
     */
    public $interval = 'monthly';

    /**
     * The number of trial days that come with the plan.
     *
     * @var int
     */
    public $trialDays;

    /**
     * The plan's price (if available).
     *
     * @var float
     */
    public $price;

    /**
     * The plan's currency (if available).
     *
     * @var string
     */
    public $currency;

    /**
     * The plan's price.
     *
     * @var int
     */
    public $rawPrice;

    /**
     * The plan's monthly incentive text.
     *
     * @var string
     */
    public $monthlyIncentive;

    /**
     * The plan's yearly incentive text.
     *
     * @var string
     */
    public $yearlyIncentive;

    /**
     * The plan's short description.
     *
     * @var string
     */
    public $description;

    /**
     * The plan's features.
     *
     * @var array
     */
    public $features = [];

    /**
     * The plan options.
     *
     * @var array
     */
    public $options;

    /**
     * Indicates if the plan is active.
     *
     * @var bool
     */
    public $active = true;


    public function __construct($name, $id)
    {
        $this->id = $id;
        $this->name = $name;
    }


    public function interval(string $interval)
    {
        $this->interval = $interval;

        return $this;
    }

    public function monthly()
    {
        $this->interval = 'monthly';

        return $this;
    }

    public function yearly()
    {
        $this->interval = 'yearly';

        return $this;
    }

    public function daily()
    {
        $this->interval = 'daily';

        return $this;
    }

    public function hourly()
    {
        $this->interval = 'hourly';

        return $this;
    }

    public function trialDays($trialDays)
    {
        $this->trialDays = $trialDays;

        return $this;
    }

    public function incentive(string $monthlyIncentive, string $yearlyIncentive)
    {
        $this->monthlyIncentive = $monthlyIncentive;
        $this->yearlyIncentive = $yearlyIncentive;

        return $this;
    }

    public function description(string $description)
    {
        $this->description = $description;

        return $this;
    }

    public function features(array $features)
    {
        $this->features = $features;

        return $this;
    }

    public function options(array $options)
    {
        $this->options = $options;

        return $this;
    }

    public function status(bool $active = true)
    {
        $this->active = $active;

        return $this;
    }

    public function archive()
    {
        $this->active = false;

        return $this;
    }

    public function jsonSerialize(): mixed
    {
        return $this->toArray();
    }

    public function toArray()
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'interval' => $this->interval,
            'price' => $this->price,
            'currency' => $this->currency,
            'raw_price' => $this->rawPrice,
            'incentive' => [
                'monthly' => $this->monthlyIncentive,
                'yearly' => $this->yearlyIncentive,
            ],
            'description' => $this->description,
            'trial_days' => $this->trialDays,
            'features' => $this->features,
            'options' => $this->options,
            'active' => $this->active,
        ];
    }
}
