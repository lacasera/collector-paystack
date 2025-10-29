# Collector PayStack

[![Latest Version on Packagist](https://img.shields.io/packagist/v/lacasera/collector-paystack.svg?style=flat-square)](https://packagist.org/packages/lacasera/collector-paystack)
[![Total Downloads](https://img.shields.io/packagist/dt/lacasera/collector-paystack.svg?style=flat-square)](https://packagist.org/packages/lacasera/collector-paystack)
[![GitHub Tests Action Status](https://img.shields.io/github/actions/workflow/status/lacasera/collector-paystack/run-tests.yml?branch=main&label=tests&style=flat-square)](https://github.com/lacasera/collector-paystack/actions?query=workflow%3Arun-tests+branch%3Amain)
[![GitHub Code Style Action Status](https://img.shields.io/github/actions/workflow/status/lacasera/collector-paystack/fix-php-code-style-issues.yml?branch=main&label=code%20style&style=flat-square)](https://github.com/lacasera/collector-paystack/actions?query=workflow%3A"Fix+PHP+code+style+issues"+branch%3Amain)

A modern Laravel package for managing subscription billing with PayStack integration. Collector PayStack provides a complete billing portal with subscription management, payment processing, and customer billing features.

## Features

- 🚀 Modern Laravel 10/11/12 support
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
- Laravel 10.0+, 11.0+, or 12.0+
- PayStack account and API keys

## Installation

You can install the package via composer:

```bash
composer require lacasera/collector-paystack
```

Publish and run the migrations:

```bash
php artisan vendor:publish --tag="collector-config"
php artisan vendor:publish --tag="collector-assets"
php artisan migrate
```

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

The billing portal is automatically available at `/billing` (configurable). Users can:

- View current subscription details
- Change subscription plans  
- Update payment methods
- View billing history
- Cancel subscriptions

### 3. Create Subscriptions Programmatically

```php
use Collector\Collector;

$user = auth()->user();

// Create a new subscription
$subscription = $user->newSubscription('default', $planId)
    ->trialDays(14)
    ->create();

// Check if user has an active subscription
if ($user->subscribed()) {
    // User has an active subscription
}

// Check for specific plan
if ($user->hasActivePlan('PLN_basic_monthly')) {
    // User is on the basic monthly plan
}
```

### 4. Handle Webhooks

The package automatically handles PayStack webhooks at `/collector/webhooks`. Make sure to configure this URL in your PayStack dashboard.

## Frontend Customization

The package includes a React-based frontend built with:

- React 18
- TypeScript
- Tailwind CSS
- Inertia.js

To customize the frontend, publish the views and assets:

```bash
php artisan vendor:publish --tag="collector-views"
php artisan vendor:publish --tag="collector-assets"
```

## Testing

```bash
composer test
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
