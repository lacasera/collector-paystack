<?php

namespace Collector\Http\Controllers;

use Collector\GuessCollectableTypes;
use Collector\RetrieveCollectableModels;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class CancelSubscriptionController
{
    use GuessCollectableTypes;
    use RetrieveCollectableModels;

    public function __invoke(Request $request)
    {
        $type = $this->guessCollectableType();

        $collectable = $this->collectable($type);

        $subscription = $collectable->subscription();

        if (! $subscription) {
            throw ValidationException::withMessages([
                '*' => __('This account does not have an active subscription.'),
            ]);
        }

        $subscription->cancel($request->get('reason'));

        return response()->json(['data' => 'Your subscription has been successfully cancelled.']);
    }
}
