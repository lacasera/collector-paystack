<?php

namespace Collector\Tests;

/**
 * Boots the package with a relocated billing portal.
 *
 * The routes are registered in the service provider's `boot()`, so the config
 * has to be in place before the container boots — setting it inside a test body
 * would be too late to affect route registration.
 */
abstract class CustomRoutePathTestCase extends TestCase
{
    protected function getEnvironmentSetUp($app): void
    {
        parent::getEnvironmentSetUp($app);

        config()->set('collector.prefix', 'account');
        config()->set('collector.path', 'subscription-settings');
    }
}
