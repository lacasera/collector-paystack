<?php

namespace Collector\Http\Rules;

use Closure;
use Collector\Collector;
use Illuminate\Contracts\Validation\ValidationRule;

class ValidPlan implements ValidationRule
{
    /**
     * @param  string  $type  The collectable plan type.
     */
    public function __construct(protected string $type) {}

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $plan = Collector::plans($this->type)
            ->first(fn($plan) => $plan->id == $value);

        if (is_null($plan) || ! $plan->active) {
            $fail('The selected plan is invalid.');
        }
    }
}
