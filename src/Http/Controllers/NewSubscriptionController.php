<?php

namespace Collector\Http\Controllers;

use Collector\Concerns\CreateSubscription;
use Collector\GuessCollectableTypes;
use Collector\Http\Rules\ValidPlan;
use Collector\RetrieveCollectableModels;
use Illuminate\Http\Request;

class NewSubscriptionController
{
    use GuessCollectableTypes;
    use RetrieveCollectableModels;

    public function __invoke(Request $request)
    {
        $type = $this->guessCollectableType();

        $collectable = $this->collectable($type);

        $request->validate(['plan' => ['required', new ValidPlan($type)]]);

        $checkout = app(CreateSubscription::class)->create($collectable, $request->plan);

        session(['spark.flash.success' => 'You have successfully subscribed to plan']);

        return response()->json(['redirect' => $checkout], 201);
    }
}
