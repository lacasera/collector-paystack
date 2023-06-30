<?php

use Collector\Http\Controllers\BillingPortalController;
use Illuminate\Support\Facades\Route;

Route::group([
    'prefix' => 'collector',
],
    function () {
        Route::group([
            //'middleware' => array_merge(config('collector.middleware', ['web', 'auth'])),
            'prefix' => config('collector.path'),
        ], function () {
            Route::get('/{type?}/{id?}', BillingPortalController::class)->name('collector.portal');   
        });
    });