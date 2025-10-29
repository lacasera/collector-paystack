<?php

namespace Collector;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Traits\Macroable;
use JsonSerializable;

class Plan implements Arrayable, JsonSerializable
{
    use Macroable;

    /**
     * Create a new plan instance.
     */
    public function __construct(
        public string $name,
        public string $id,
        public string $interval = 'monthly',
        public ?int $trialDays = null,
        public ?string $price = null,
        public ?string $currency = null,
        public ?int $rawPrice = null,
        public string $monthlyIncentive = '',
        public string $yearlyIncentive = '',
        public string $description = '',
        public array $features = [],
        public array $options = [],
        public bool $active = true
    ) {}

    public function interval(string $interval): self
    {
        $this->interval = $interval;

        return $this;
    }

    public function monthly(): self
    {
        return $this->interval('monthly');
    }

    public function yearly(): self
    {
        return $this->interval('yearly');
    }

    public function daily(): self
    {
        return $this->interval('daily');
    }

    public function hourly(): self
    {
        return $this->interval('hourly');
    }

    public function trialDays(?int $trialDays): self
    {
        $this->trialDays = $trialDays;

        return $this;
    }

    public function incentive(string $monthlyIncentive, string $yearlyIncentive): self
    {
        $this->monthlyIncentive = $monthlyIncentive;
        $this->yearlyIncentive = $yearlyIncentive;

        return $this;
    }

    public function description(string $description): self
    {
        $this->description = $description;

        return $this;
    }

    public function features(array $features): self
    {
        $this->features = $features;

        return $this;
    }

    public function options(array $options): self
    {
        $this->options = $options;

        return $this;
    }

    public function status(bool $active = true): self
    {
        $this->active = $active;

        return $this;
    }

    public function archive(): self
    {
        return $this->status(false);
    }

    public function jsonSerialize(): array
    {
        return $this->toArray();
    }

    public function toArray(): array
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
