<?php

use App\Models\User;

return [

    /*
    |--------------------------------------------------------------------------
    | Collector Domain
    |--------------------------------------------------------------------------
    |
    | This configuration option determines the domain the Collector routes are
    | registered on. Leaving this null serves the billing portal from your
    | application's current domain. Set it to host billing elsewhere,
    | such as a dedicated "billing.example.com" subdomain.
    |
    */

    'domain' => env('COLLECTOR_DOMAIN'),

    /*
    |--------------------------------------------------------------------------
    | Collector Path
    |--------------------------------------------------------------------------
    |
    | Together these options determine the URI at which the Collector billing
    | portal is available. The portal is served from "prefix/path", so the
    | values below resolve to "/collector/billing" out of the box. Both
    | are yours to change to fit your application's URL structure.
    |
    | The subscribe and cancel endpoints live directly under the prefix, at
    | "prefix/subscription" and "prefix/subscription/cancel". Nothing in
    | the package hard-codes these URIs, so you may move them freely.
    |
    */

    'prefix' => env('COLLECTOR_PREFIX', 'collector'),

    'path' => 'billing',

    /*
    |--------------------------------------------------------------------------
    | Collector Webhook Path
    |--------------------------------------------------------------------------
    |
    | This is the URI PayStack posts webhook events to. It is deliberately kept
    | independent of the prefix above: the URL is registered by hand in your
    | PayStack dashboard, so moving the billing portal must never move the
    | webhook out from under it. Change this only when you are ready to
    | update the corresponding URL in your PayStack dashboard too.
    |
    */

    'webhook_path' => 'collector/webhooks',

    'secret' => env('PAYSTACK_SECRET_KEY'),

    /*
    |--------------------------------------------------------------------------
    | Collector Middleware
    |--------------------------------------------------------------------------
    |
    | These are the middleware that requests to the Collector billing portal must
    | pass through before being accepted. Typically, the default list that
    | is defined below should be suitable for most Laravel applications.
    |
    */

    'middleware' => ['web', 'auth'],

    /*
    |--------------------------------------------------------------------------
    | Branding
    |--------------------------------------------------------------------------
    |
    | These configuration values allow you to customize the branding of the
    | billing portal, including the primary color and the logo that will
    | be displayed within the billing portal. This logo value must be
    | the absolute path to an SVG logo within the local filesystem.
    |
    */

    // 'brand' =>  [
    //     'logo' => realpath(__DIR__.'/../public/svg/billing-logo.svg'),
    //     'color' => 'bg-gray-800',
    // ],

    /*
    |--------------------------------------------------------------------------
    | Collector Collectables
    |--------------------------------------------------------------------------
    |
    | Below you may define collectable entities supported by your Collector driven
    | application. The paystack edition of Collector currently only supports a
    | single collectable model entity (team, user, etc.) per application.
    |
    | In addition to defining your collectable entity, you may also define its
    | plans and the plan's features, including a short description of it
    | as well as a "bullet point" listing of its distinctive features.
    |
    */

    'currency' => env('COLLECTOR_CURRENCY', 'GHS'),

    'collectables' => [
        'user' => [
            'model' => User::class,
            'trial_days' => 5,
            'default_interval' => 'monthly',
            'plans' => [
                [
                    'name' => 'Basic',
                    'description' => 'This is a short, human friendly description of the plan.',
                    'monthly_id' => 'PLN_worid7k3e8v5afz',
                    'yearly_incentive' => 'Save 10%',
                    'yearly_id' => 'PLN_2y8oe4r1gx7gakr',
                    'features' => [
                        'Feature 1',
                        'Feature 2',
                        'Feature 3',
                    ],
                    'options' => [

                    ],
                ],
                [
                    'name' => 'Standard',
                    'description' => 'This is a short, human friendly description of the plan.',
                    'monthly_id' => 'PLN_wc54sx7clavvy6d',
                    'yearly_id' => 'PLN_b9kvd76fufw4vu9',
                    'yearly_incentive' => 'Save 10%',
                    'features' => [
                        'Feature 1',
                        'Feature 2',
                        'Feature 3',
                    ],
                ],
                [
                    'name' => 'Premium',
                    'description' => 'This is a short, human friendly description of the plan.',
                    'monthly_id' => 'PLN_g47cv05s5jsz29k',
                    'yearly_id' => null,
                    'features' => [
                        'Feature 1',
                        'Feature 2',
                        'Feature 3',
                    ],
                ],
                [
                    'name' => 'Unlimited',
                    'description' => 'This is a short, human friendly description of the plan.',
                    'monthly_id' => 'PLN_l2qz2ab1wjhh4yx',
                    'yearly_id' => null,
                    'features' => [
                        'Feature 1',
                        'Feature 2',
                        'Feature 3',
                    ],
                ],
            ],

        ],
    ],

    /**
     * This will be shown on the cancelation modal
     */
    'cancelation' => [
        'heading' => 'Are you sure you want to cancel your subscription?',
        'subText' => 'Once your subscription is cancelled, all of its resources and related data will be permanently deleted.',
        'reasonLabel' => 'Please why you canceling',
    ],
];
