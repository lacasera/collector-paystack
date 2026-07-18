<?php

use Collector\Tests\CustomRoutePathTestCase;
use Collector\Tests\TestCase;

/*
|--------------------------------------------------------------------------
| Test Case
|--------------------------------------------------------------------------
|
| Bind the package TestCase (Orchestra Testbench) to every test in the
| Unit and Feature suites so they boot the Laravel container, run the
| package migrations, and share the Paystack HTTP fakes.
|
*/

uses(TestCase::class)->in('Feature', 'Unit');

/*
| The Routing suite boots the package with a relocated billing portal, which
| has to be configured before the container boots — hence its own test case
| and its own directory, since a suite may only bind one.
*/
uses(CustomRoutePathTestCase::class)->in('Routing');
