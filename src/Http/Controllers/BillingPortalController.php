<?php

namespace Collector\Http\Controllers;

use Collector\Collector;
use Collector\Events\PaymentVerified;
use Collector\FrontendState;
use Collector\GuessCollectableTypes;
use Collector\RetrieveCollectableModels;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\View;
use Inertia\Inertia;

class BillingPortalController
{
    use GuessCollectableTypes;
    use RetrieveCollectableModels;

    public function __invoke(Request $request, $type = null, $id = null)
    {
        $type = $type ?: $this->guessCollectableType();

        $collectable = $this->collectable($type, $id);
        Inertia::setRootView('collector::app');

        View::share([
            'cssPath' => __DIR__.'/../../../public/css/app.css',
            'jsPath' => __DIR__.'/../../../public/js/app.js',
        ]);

        Inertia::share(app(FrontendState::class)->current($type, $collectable));

        if ($request->has('reference')) {
            PaymentVerified::dispatch($collectable, $request->get('reference'));
        }

        if ($collectable->currentActivePlan() && !$request->has('change')) {
            $plans = Collector::plans($type);
            return Inertia::render('Index', [
                'currentPlan' => $plans->where('id', $collectable->currentActivePlan()?->paystack_plan)->first(),
                'paystackCustomer' => $collectable->getAsPaystackCustomer(),
                'nextBillingDate' => $collectable->currentActivePlan()->getNextBillingDate()->format('jS F, Y'),
                'history' => $collectable->transctionHistroy()
            ]);
        }

        return Inertia::render('Plans', [
            'subscribed' => $collectable->currentActivePlan()?->paystack_plan,
        ]);
    }
}
