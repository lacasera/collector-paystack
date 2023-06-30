<?php

namespace Collector\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\View;
use Inertia\Inertia;

class BillingPortalController
{
    public function __invoke(Request $request)
    {
        Inertia::setRootView('collector::app');

        View::share([
            'cssPath' => __DIR__.'/../../../public/css/app.css',
            'jsPath' => __DIR__.'/../../../public/js/app.js',
        ]);

        // Index.jsx ----> Subscribed (or user with a Subscription)
        // Plans.jsx ----> when user is not subscribe (or user wants to change Subscription)
        return Inertia::render('Plans', [
            'subscribe' => true
        ]);
    }
}