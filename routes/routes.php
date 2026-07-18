<?php

use Collector\Http\Controllers\BillingManagementController;
use Collector\Http\Controllers\BillingPortalController;
use Collector\Http\Controllers\CancelSubscriptionController;
use Collector\Http\Controllers\CollectorWebhookController;
use Collector\Http\Controllers\NewSubscriptionController;
use Collector\Http\Controllers\UpdatePaymentMethodController;
use Collector\Http\Middleware\HandleInertiaRequests;
use Illuminate\Support\Facades\Route;

Route::group(array_filter([
    'domain' => config('collector.domain'),
    'prefix' => config('collector.prefix', 'collector'),
]), function () {
    Route::group([
        'middleware' => array_merge(config('collector.middleware', ['web', 'auth']), [HandleInertiaRequests::class]),
        'prefix' => config('collector.path'),
    ], function () {
        // Declared before the portal route below, whose optional {type?}
        // segment would otherwise swallow "manage" as a collectable type.
        Route::get('/manage', BillingManagementController::class)->name('collector.manage');

        Route::get('/{type?}/{id?}', BillingPortalController::class)->name('collector.portal');
    });

    Route::group(['middleware' => config('collector.middleware', ['web', 'auth'])], function () {
        Route::post('/subscription', NewSubscriptionController::class)->name('collector.new-subscription');
        Route::post('/subscription/cancel', CancelSubscriptionController::class)->name('collector.cancel-subscription');
        Route::post('/payment-method', UpdatePaymentMethodController::class)->name('collector.update-payment-method');
    });
});

/*
 * Registered outside the group above so it keeps its own path. PayStack is told
 * this URL out-of-band via the dashboard, so relocating the portal must not
 * silently move the endpoint PayStack is already posting to.
 */
Route::post(config('collector.webhook_path', 'collector/webhooks'), CollectorWebhookController::class)
    ->name('collector.webhook');
