<?php

namespace Collector\Http\Controllers;

use Collector\GuessCollectableTypes;
use Collector\RetrieveCollectableModels;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Hand the customer off to PayStack's hosted subscription management page.
 *
 * PayStack exposes no API for replacing a stored card, so the card update is
 * delegated. The link is short lived, so it is minted per request rather than
 * cached, and the card details never reach this application.
 */
class UpdatePaymentMethodController
{
    use GuessCollectableTypes;
    use RetrieveCollectableModels;

    public function __invoke(Request $request): JsonResponse
    {
        $collectable = $this->collectable($this->guessCollectableType());

        if (! $subscription = $collectable->currentActivePlan()) {
            return response()->json(['message' => 'No active subscription to update.'], 422);
        }

        if (! $link = $collectable->subscriptionManageLink($subscription->paystack_id)) {
            return response()->json(['message' => 'Could not reach PayStack. Please try again.'], 502);
        }

        return response()->json(['redirect' => $link]);
    }
}
