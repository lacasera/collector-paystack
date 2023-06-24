<?php

namespace Collector\Http\Controllers;

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

       // dd($collectable->createAsPayStackCustomer([]));
        View::share([
            'cssPath' => __DIR__.'/../../../public/css/app.css',
            'jsPath' => __DIR__.'/../../../public/js/app.js',
        ]);

        Inertia::share(app(FrontendState::class)->current($type, $collectable));

        // Index.jsx ----> Subscribed (or user with a Subscription)
        // Plans.jsx ----> when user is not subscribe (or user wants to change Subscription)
        return Inertia::render('Plans', [
            'subscribe' => true
        ]);
    }
}
