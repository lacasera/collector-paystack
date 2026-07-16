<?php

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
