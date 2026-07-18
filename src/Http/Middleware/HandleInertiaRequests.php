<?php

namespace Collector\Http\Middleware;

use Illuminate\Http\Request;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    /**
     * Determine the current asset version.
     *
     * @see https://inertiajs.com/asset-versioning
     *
     * @return string|null
     */
    public function version(Request $request)
    {
        return 'collector-' . parent::version($request);
    }

    /**
     * Defines the props that are shared by default.
     *
     * @see https://inertiajs.com/shared-data
     *
     * @return array
     */
    public function share(Request $request)
    {
        return array_merge(parent::share($request), [
            'collector' => [
                // The bundle is pre-built and shipped with the package, so a
                // build-time value could never reflect the installing app —
                // the name has to come from the server at request time.
                'appName' => config('app.name'),
                'flash' => [
                    'success' => fn() => $request->session()->pull('collector.flash.success'),
                    'error' => fn() => $request->session()->pull('collector.flash.error'),
                ],
                // Resolved from the named routes so the frontend follows the
                // configured prefix instead of hard-coding "/collector".
                'urls' => [
                    // The host application's root, which may not sit at "/"
                    // when the app is served from a subdirectory.
                    'home' => url('/'),
                    'subscribe' => route('collector.new-subscription'),
                    'cancel' => route('collector.cancel-subscription'),
                    'portal' => route('collector.portal'),
                    'manage' => route('collector.manage'),
                    // The portal forwards subscribers to the management page;
                    // this flag is what lets them back in to switch plans.
                    'changePlan' => route('collector.portal', ['change' => 1]),
                    'updatePaymentMethod' => route('collector.update-payment-method'),
                ],
            ],
        ]);
    }
}
