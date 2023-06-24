<?php

use Illuminate\Support\Facades\Route;
use Collector\Http\Middleware\HandleInertiaRequests;

Route::group(['prefix' => 'collector'], function () {
    Route::group([
        'namespace' => 'Collector\Http\Controllers',
        'middleware' => array_merge(config('collector.middleware', ['web', 'auth']), [HandleInertiaRequests::class]),
        'prefix' => config('collector.path'),
    ], function () {
        Route::get('/{type?}/{id?}', 'BillingPortalController')->name('collector.portal');
    });
});

Route::group(['namespace' => 'Collector\Http\Controllers', 'prefix' => 'collector'], function () {
    Route::group(['middleware' => array_merge(config('collector.middleware', ['web', 'auth']))], function () {
        Route::post('/subscription', 'NewSubscriptionController');
    });
});