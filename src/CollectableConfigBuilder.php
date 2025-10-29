<?php

namespace Collector;

class CollectableConfigBuilder
{
    protected $type;

    public function __construct(string $type)
    {
        $this->type = $type;
    }

    public function resolve(callable $callback)
    {
        Collector::resolveCollectableUsing($this->type, $callback);

        return $this;
    }

    public function authorize(callable $callback)
    {
        Collector::authorizeUsing($this->type, $callback);

        return $this;
    }

    public function checkPlanEligibility(callable $callback)
    {
        Collector::checkPlanEligibilityUsing($this->type, $callback);

        return $this;
    }


    public function chargePerSeat(string $seatName, callable $callback)
    {
        Collector::chargePerSeat($this->type, $seatName, $callback);

        return $this;
    }

    public function plan($name, $id)
    {
        return Collector::plan($this->type, $name, $id);
    }
}
