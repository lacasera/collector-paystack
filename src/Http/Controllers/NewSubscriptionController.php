<?php

namespace Collector\Http\Controllers;

use Collector\Concerns\CreateSubscription;
use Collector\Http\Rules\ValidPlan;
use Collector\RetrieveCollectableModels;
use Illuminate\Http\Request;

class NewSubscriptionController
{
    use RetrieveCollectableModels;

    public function __invoke(Request $request)
    {
        $collectable = $this->collectable();

        $request->validate(['plan' => ['required', new ValidPlan($request->collectableType)]]);

      //  return response()->json(['data' => 'ok']);
        $checkout = app(CreateSubscription::class)->create($collectable, $request->plan);

        return response()->json([
            'redirect' => $checkout->url,
        ]);
        //$validator =
    }
}
