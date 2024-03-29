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
        return 'collector-'.parent::version($request);
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
                'flash' => [
                    'success' => fn () => $request->session()->pull('collector.flash.success'),
                    'error' => fn () => $request->session()->pull('collector.flash.error'),
                ],
            ],
        ]);
    }
}
