<?php

use Collector\Events\PaymentVerified;
use Collector\Models\Subscription;
use Collector\Tests\RestrictedTestUser;

/**
 * The package writes its own billing columns onto the host application's model.
 * That model's $fillable belongs to the application, not to us, so every such
 * write has to bypass mass-assignment protection — otherwise the columns are
 * dropped silently and the subscription never materialises.
 *
 * These use {@see RestrictedTestUser}, whose $fillable covers only name/email/
 * password, exactly as the stock Laravel skeleton ships.
 */
function restricted_user(): RestrictedTestUser
{
    $user = new RestrictedTestUser();

    $user->forceFill([
        'name' => 'Ada Lovelace',
        'email' => 'ada@example.com',
        'password' => 'secret',
    ])->save();

    return $user->refresh();
}

it('persists billing columns on a model with a restrictive fillable', function () {
    fake_paystack();

    $user = restricted_user();

    expect($user->isFillable('paystack_id'))->toBeFalse();

    $user->createAsPayStackCustomer();

    expect($user->refresh()->paystack_id)->toBe('CUS_test123')
        ->and($user->pm_type)->toBe('visa')
        ->and($user->pm_last_four)->toBe('4081');
});

it('records the subscription for a collectable that has no paystack id yet', function () {
    fake_paystack();

    // No ->withPaystackId(): the customer code has to come off the verified
    // transaction, or SyncSubscription bails and nothing is ever recorded.
    $user = restricted_user();

    expect($user->hasPayStackId())->toBeFalse();

    PaymentVerified::dispatch($user, 'REF_test123');

    expect($user->refresh()->paystack_id)->toBe('CUS_test123');

    $subscription = Subscription::where('user_id', $user->id)->first();

    expect($subscription)->not->toBeNull()
        ->and($subscription->paystack_plan)->toBe('PLN_worid7k3e8v5afz')
        ->and($subscription->paystack_status)->toBe('active');
});

it('surfaces the recorded plan as the current active plan', function () {
    fake_paystack();

    $user = restricted_user();

    PaymentVerified::dispatch($user, 'REF_test123');

    // This is the value the portal compares each plan card against, so a null
    // here is what leaves the subscribed plan unhighlighted.
    expect($user->refresh()->currentActivePlan()?->paystack_plan)
        ->toBe('PLN_worid7k3e8v5afz');
});
