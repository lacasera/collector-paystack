<?php

namespace Collector\Http\Controllers;

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

        if ($request->has('reference')) {
            PaymentVerified::dispatch($collectable, $request->get('reference'));

            // The listener runs synchronously, so the subscription it records is
            // already visible — which is what makes this the first honest point
            // to report the outcome to the customer.
            session($collectable->fresh()?->currentActivePlan()
                ? ['collector.flash.success' => 'Your subscription is now active.']
                : ['collector.flash.error' => 'We could not confirm that payment. Please try again.']);

            // Strip the reference so a reload cannot re-run verification.
            return redirect()->to($request->url());
        }

        if (! $request->boolean('change') && $collectable->currentActivePlan()) {
            return redirect()->route('collector.manage');
        }

        Inertia::setRootView('collector::app');

        View::share([
            'cssPath' => __DIR__ . '/../../../public/css/collector.css',
            'jsPath' => __DIR__ . '/../../../public/js/app.js',
        ]);

        Inertia::share(app(FrontendState::class)->current($type, $collectable));

        return Inertia::render('Plans', [
            'subscribed' => $collectable->currentActivePlan()?->paystack_plan,
        ]);
    }
}
