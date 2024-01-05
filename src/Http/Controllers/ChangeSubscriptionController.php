<?php

namespace Collector\Http\Controllers;

use Collector\Concerns\CreateSubscription;
use Collector\GuessCollectableTypes;
use Collector\Http\Rules\ValidPlan;
use Collector\RetrieveCollectableModels;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class ChangeSubscriptionController
{
    use GuessCollectableTypes;
    use RetrieveCollectableModels;

    public function __invoke(Request $request)
    {
        $type = $this->guessCollectableType();

        $collectable = $this->collectable($type);

        $subscription = $collectable->subscription();

        $request->validate(['plan' => ['required', new ValidPlan($type)]]);

        if (! $subscription) {
            throw ValidationException::withMessages([
                '*' => __('This account does not have an active subscription.'),
            ]);
        }

        /**
         * 1. get the current plan price
         * 2. get the new plan price
         * 3. let the user pay the balance but creating a transaction
         * 4. create a subscription with the new plan id
         */
       // $checkout = app(CreateSubscription::class)->create($collectable, $request->plan);

        session(['spark.flash.success' => 'You have successfully subscribed to plan']);

        return response()->json(['redirect' => 'this is good'], 201);
    }
}
