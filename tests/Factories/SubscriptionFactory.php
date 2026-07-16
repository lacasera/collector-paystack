<?php

namespace Collector\Tests\Factories;

use Collector\Models\Subscription;
use Collector\Tests\TestUser;
use Illuminate\Database\Eloquent\Factories\Factory;

class SubscriptionFactory extends Factory
{
    protected $model = Subscription::class;

    public function definition(): array
    {
        return [
            'user_id' => UserFactory::new(),
            'name' => 'Basic',
            'paystack_id' => 'SUB_' . $this->faker->unique()->bothify('##########'),
            'paystack_status' => Subscription::ACTIVE_STATUS,
            'paystack_plan' => 'PLN_worid7k3e8v5afz',
            'paystack_email_token' => $this->faker->sha1(),
            'quantity' => 1,
            'trial_ends_at' => null,
            'ends_at' => null,
        ];
    }

    public function forUser(TestUser $user): static
    {
        return $this->state(fn() => ['user_id' => $user->id]);
    }

    public function cancelled(): static
    {
        return $this->state(fn() => [
            'paystack_status' => Subscription::CANCELLED_STATUS,
            'ends_at' => now()->subDay(),
        ]);
    }

    public function onGracePeriod(): static
    {
        return $this->state(fn() => [
            'paystack_status' => Subscription::CANCELLED_STATUS,
            'ends_at' => now()->addDays(10),
        ]);
    }

    public function onTrial(int $days = 5): static
    {
        return $this->state(fn() => [
            'paystack_status' => Subscription::TRIALING_STATUS,
            'trial_ends_at' => now()->addDays($days),
        ]);
    }
}
