<?php

namespace Collector\Tests\Unit;

use Collector\Plan;
use Collector\Tests\TestCase;

class PlanTest extends TestCase
{
    public function test_can_create_plan_with_constructor_parameters(): void
    {
        $plan = new Plan(
            name: 'Basic Plan',
            id: 'plan_123',
            interval: 'monthly',
            trialDays: 14,
            description: 'A basic plan for testing',
            features: ['Feature 1', 'Feature 2'],
            active: true
        );

        $this->assertEquals('Basic Plan', $plan->name);
        $this->assertEquals('plan_123', $plan->id);
        $this->assertEquals('monthly', $plan->interval);
        $this->assertEquals(14, $plan->trialDays);
        $this->assertEquals('A basic plan for testing', $plan->description);
        $this->assertEquals(['Feature 1', 'Feature 2'], $plan->features);
        $this->assertTrue($plan->active);
    }

    public function test_can_set_plan_properties_with_fluent_methods(): void
    {
        $plan = (new Plan('Test Plan', 'plan_456'))
            ->yearly()
            ->trialDays(30)
            ->description('Test description')
            ->features(['Feature A', 'Feature B'])
            ->options(['option1' => 'value1'])
            ->incentive('Save 10%', 'Save 20%');

        $this->assertEquals('yearly', $plan->interval);
        $this->assertEquals(30, $plan->trialDays);
        $this->assertEquals('Test description', $plan->description);
        $this->assertEquals(['Feature A', 'Feature B'], $plan->features);
        $this->assertEquals(['option1' => 'value1'], $plan->options);
        $this->assertEquals('Save 10%', $plan->monthlyIncentive);
        $this->assertEquals('Save 20%', $plan->yearlyIncentive);
    }

    public function test_can_convert_plan_to_array(): void
    {
        $plan = (new Plan('Test Plan', 'plan_789'))
            ->monthly()
            ->description('Test description')
            ->features(['Feature 1']);

        $array = $plan->toArray();

        $this->assertIsArray($array);
        $this->assertEquals('Test Plan', $array['name']);
        $this->assertEquals('plan_789', $array['id']);
        $this->assertEquals('monthly', $array['interval']);
        $this->assertEquals('Test description', $array['description']);
        $this->assertEquals(['Feature 1'], $array['features']);
        $this->assertTrue($array['active']);
    }

    public function test_can_archive_plan(): void
    {
        $plan = new Plan('Test Plan', 'plan_archive');

        $this->assertTrue($plan->active);

        $plan->archive();

        $this->assertFalse($plan->active);
    }

    public function test_plan_implements_json_serializable(): void
    {
        $plan = (new Plan('JSON Plan', 'plan_json'))
            ->description('JSON serializable plan');

        $json = json_encode($plan);
        $decoded = json_decode($json, true);

        $this->assertIsString($json);
        $this->assertEquals('JSON Plan', $decoded['name']);
        $this->assertEquals('plan_json', $decoded['id']);
        $this->assertEquals('JSON serializable plan', $decoded['description']);
    }
}
