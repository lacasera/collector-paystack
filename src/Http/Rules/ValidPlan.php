<?php

namespace Collector\Http\Rules;

use Collector\Collector;
use Illuminate\Contracts\Validation\Rule;

class ValidPlan implements Rule
{
    /**
     * The plan type.
     *
     * @var string
     */
    protected $type;

    /**
     * Create a new rule instance.
     *
     * @param  string  $type
     */
    public function __construct($type)
    {
        $this->type = $type;
    }

    public function message()
    {
        return "The selected plan is invalid";
    }

    public function passes($attribute, $value)
    {
        $plan = Collector::plans($this->type)
            ->first(function ($plan) use ($value) {
                return $plan->id == $value;
            });

        return ! is_null($plan) && $plan->active;
    }
}
