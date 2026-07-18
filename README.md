# Collector PayStack

[![Latest Version on Packagist](https://img.shields.io/packagist/v/lacasera/collector-paystack.svg?style=flat-square)](https://packagist.org/packages/lacasera/collector-paystack)
[![Total Downloads](https://img.shields.io/packagist/dt/lacasera/collector-paystack.svg?style=flat-square)](https://packagist.org/packages/lacasera/collector-paystack)
[![GitHub Tests Action Status](https://img.shields.io/github/actions/workflow/status/lacasera/collector-paystack/run-tests.yml?branch=main&label=tests&style=flat-square)](https://github.com/lacasera/collector-paystack/actions?query=workflow%3Arun-tests+branch%3Amain)
[![GitHub Code Style Action Status](https://img.shields.io/github/actions/workflow/status/lacasera/collector-paystack/fix-php-code-style-issues.yml?branch=main&label=code%20style&style=flat-square)](https://github.com/lacasera/collector-paystack/actions?query=workflow%3A"Fix+PHP+code+style+issues"+branch%3Amain)

A modern Laravel package for managing subscription billing with PayStack integration. Collector PayStack provides a complete billing portal with subscription management, payment processing, and customer billing features.

## Features

- 🚀 Modern Laravel 10/11/12/13 support
- 💳 Complete PayStack integration
- 📊 Subscription management (create, update, cancel)
- 🎨 Beautiful React-based billing portal  
- 📅 Multiple billing intervals (monthly, yearly, daily, hourly)
- 🔄 Trial periods and grace periods
- 📧 Webhook handling for payment verification
- 🎯 Plan switching capabilities
- 📱 Responsive design with Tailwind CSS

## Requirements

- PHP 8.2+
- Laravel 10.0+, 11.0+, 12.0+, or 13.0+
- PayStack account and API keys

## Installation

Install the package via composer:

```bash
composer require lacasera/collector-paystack
```

Then run the interactive installer, which publishes the config and assets and
runs the migrations:

```bash
php artisan collector:install
```

Prefer to do it by hand? The installer is equivalent to:

```bash
php artisan vendor:publish --tag="collector-config"   # config/collector.php
php artisan vendor:publish --tag="collector-assets"   # public/vendor/collector
php artisan vendor:publish --tag="collector-views"    # optional: resources/views/vendor/collector
php artisan migrate
```

> The migrations add billing columns to your `users` table (`paystack_id`,
> `pm_type`, `trial_ends_at`, …) and create a `subscriptions` table.

## Configuration

Add your PayStack credentials to your `.env` file:

```env
PAYSTACK_SECRET_KEY=your_paystack_secret_key
COLLECTOR_CURRENCY=NGN
```

Configure your subscription plans in `config/collector.php`:

```php
'collectables' => [
    'user' => [
        'model' => App\Models\User::class,
        'trial_days' => 14,
        'default_interval' => 'monthly',
        'plans' => [
            [
                'name' => 'Basic',
                'description' => 'Perfect for getting started',
                'monthly_id' => 'PLN_your_monthly_plan_id',
                'yearly_id' => 'PLN_your_yearly_plan_id',
                'yearly_incentive' => 'Save 20%',
                'features' => [
                    'Up to 10 projects',
                    '5GB storage',
                    'Email support',
                ],
            ],
            // More plans...
        ],
    ],
],
```

## Usage

### 1. Prepare Your User Model

Add the `Collectable` trait to your User model:

```php
<?php

namespace App\Models;

use Collector\Collectable;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{
    use Collectable;
    
    // Your existing model code...
}
```

### 2. Access the Billing Portal

The billing portal is automatically available at `/collector/billing`. From the
portal users can:

- Browse the configured plans and toggle between monthly and yearly pricing
- Subscribe to a plan (or switch plans)
- Cancel their current subscription

#### Customising the portal URL

The portal URI is built from `prefix` + `path`, and both are yours to change in
`config/collector.php`:

```php
'domain' => env('COLLECTOR_DOMAIN'),        // null = your app's current domain
'prefix' => env('COLLECTOR_PREFIX', 'collector'),
'path'   => 'billing',
```

| Config | Portal URL |
| --- | --- |
| defaults | `/collector/billing` |
| `'prefix' => 'account'` | `/account/billing` |
| `'prefix' => 'account', 'path' => 'subscription'` | `/account/subscription` |
| `'domain' => 'billing.example.com', 'prefix' => null` | `https://billing.example.com/billing` |

The subscribe and cancel endpoints move with the prefix, and the billing portal
frontend receives them as shared Inertia props — so nothing needs rebuilding
when you change these values.

Always link to the portal with the route name rather than a literal path, so
your links follow the config:

```php
route('collector.portal');   // or: $user->billingPortalUrl()
```

> **The webhook URL does not move with the prefix.** It has its own
> `webhook_path` setting, because PayStack learns that URL out-of-band from your
> dashboard — relocating the portal must not silently break an endpoint PayStack
> is already posting to. See [Handle Webhooks](#6-handle-webhooks).

### 3. Query Subscription State

The `Collectable` trait and the `Subscription` model expose the state you need:

```php
$user = auth()->user();

// Is the user subscribed at all?
$user->subscribed();

// Subscribed to a specific PayStack plan?  (Cashier's subscribedToPrice)
$user->subscribedToPlan('PLN_basic_monthly');

// Subscribed to any plan under a product (the plan-name group in config)?
$user->subscribedToProduct('Basic');

// The user's active subscription (or null)
$subscription = $user->subscription();

// Returns the matching Subscription (or null)
$user->hasActivePlan('PLN_basic_monthly');

// The user's current active plan code, and all subscriptions (most recent first)
$user->currentActivePlan()?->paystack_plan;
$user->subscriptions;

// Inspecting a subscription
$subscription?->isActive();
$subscription?->onTrial();
$subscription?->onGracePeriod();
$subscription?->valid();      // active, on trial, or within grace period
```

### Customers & Payment Methods

```php
// Create / update the PayStack customer
$user->createAsPayStackCustomer(['email' => $user->email]);
$user->createOrGetPayStackCustomer();
$user->updatePayStackCustomer(['first_name' => 'Ada']);
$user->hasPayStackId();

// Payment methods (from the PayStack customer's authorizations)
$user->paymentMethods();
$user->defaultPaymentMethod();   // the stored default card
$user->hasPaymentMethod();

// The in-app billing portal URL
$url = $user->billingPortalUrl();
```

### 4. Start a Subscription

Subscriptions are created through PayStack's hosted checkout. The billing portal
does this for you when a user picks a plan, but you can also start the flow
yourself with a fluent, Cashier-style builder. `checkout()` returns a redirect to
PayStack:

```php
return $request->user()
    ->newSubscription('default', 'PLN_basic_monthly')
    ->trialDays(5)
    ->checkout([
        'success_url' => route('billing.success'),
    ]);
```

- `success_url` maps to PayStack's `callback_url`; omit it to return to the
  built-in billing portal, which verifies and records the payment automatically.
- `trialDays()` records a trial period on the resulting subscription.
- Starting a new subscription cancels the user's existing active subscription on
  PayStack (plan switching).

Need the raw URL instead of a redirect (e.g. for a JSON/API response)? Cast the
result to a string:

```php
$url = (string) $user->newSubscription('default', 'PLN_basic_monthly')->checkout();
```

### 5. Cancel a Subscription

```php
$user->subscription()?->cancel('No longer needed');
```

Cancelling disables the subscription on PayStack and keeps it valid until the end
of the current billing period (grace period).

### 6. Handle Webhooks

The package registers a webhook endpoint at **`POST /collector/webhooks`**. Add
this URL to your PayStack dashboard (Settings → API Keys & Webhooks). When
`PAYSTACK_SECRET_KEY` is set, every request is verified against PayStack's
`x-paystack-signature` header before it is processed.

This path is set by `collector.webhook_path` and is deliberately independent of
the portal's `prefix`, so moving the billing portal leaves the webhook where
your PayStack dashboard expects it. If you do change `webhook_path`, update the
URL in the dashboard at the same time or events will start 404ing. The endpoint
is named, so you can always resolve it with `route('collector.webhook')`.

Handled events: `subscription.create`, `subscription.not_renew`,
`charge.success`, `invoice.create`, and `invoice.payment_failed`.

## Events

Collector dispatches events throughout the billing lifecycle so you can hook in
your own logic (send receipts, provision access, notify Slack, …):

| Event | Dispatched when |
| --- | --- |
| `Collector\Events\WebhookReceived` | Any signed PayStack webhook is received |
| `Collector\Events\PaymentReceived` | A `charge.success` / failed-invoice webhook arrives |
| `Collector\Events\InvoiceCreated` | An `invoice.create` webhook arrives |
| `Collector\Events\PaymentVerified` | A user returns from checkout with a `reference` |
| `Collector\Events\SubscriptionCanceled` | A subscription is cancelled |

Listen for them as usual:

```php
use Collector\Events\SubscriptionCanceled;
use Illuminate\Support\Facades\Event;

Event::listen(SubscriptionCanceled::class, function (SubscriptionCanceled $event) {
    // $event->collectable, $event->subscription
});
```

## Authorization & Custom Resolution

By default the billing portal resolves the collectable from the authenticated
user and allows access to anyone signed in. Override either behaviour from a
service provider:

```php
use App\Models\User;
use Collector\Collector;
use Illuminate\Http\Request;

public function boot(): void
{
    // Resolve which model the portal manages
    Collector::collectable(User::class)->resolve(fn (Request $request) => $request->user());

    // Authorize who may view the portal
    Collector::collectable(User::class)->authorize(
        fn (User $collectable, Request $request) => $request->user()?->is($collectable)
    );
}
```

You can also change the portal path and its middleware in `config/collector.php`
(`path` and `middleware`).

### Custom Models

If you extend the package's models, register them from a service provider (a
single `useCustomerModel()` call configures both collectable resolution and the
`Subscription → owner` relationship):

```php
use Collector\Collector;

Collector::useCustomerModel(\App\Models\User::class);
Collector::useSubscriptionModel(\App\Models\Subscription::class);
```

## Frontend Customization

The package ships a **pre-built** React portal (React 18 + TypeScript + Tailwind +
Inertia.js); the compiled assets are inlined into the portal page, so no build
step is required to use it.

To tweak the Blade shell, publish the views:

```bash
php artisan vendor:publish --tag="collector-views"
```

To modify the React source, edit `resources/js` and rebuild the bundle:

```bash
npm install
npm run build   # emits public/js/app.js and public/css/collector.css
```

## Testing

PHP (Pest / Testbench):

```bash
composer test              # run the suite
composer test-coverage     # with a coverage report
```

Frontend and end-to-end:

```bash
npm run type-check         # TypeScript
npm run test               # Vitest component tests
npm run test:e2e           # Playwright E2E against a served workbench app
```

### Code Style

```bash
composer pint
```

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) for details.

## Security Vulnerabilities

If you discover any security related issues, please email aboateng62@gmail.com instead of using the issue tracker.

## Credits

- [Agyenim Boateng](https://github.com/lacasera)
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
