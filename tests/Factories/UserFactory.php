<?php

namespace Collector\Tests\Factories;

use Collector\Tests\TestUser;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;

class UserFactory extends Factory
{
    protected $model = TestUser::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->name(),
            'email' => $this->faker->unique()->safeEmail(),
            'password' => Hash::make('password'),
            'paystack_id' => null,
            'trial_ends_at' => null,
        ];
    }

    /**
     * Indicate the user is already a registered PayStack customer.
     */
    public function withPaystackId(string $customerCode = 'CUS_test123'): static
    {
        return $this->state(fn() => [
            'paystack_id' => $customerCode,
        ]);
    }

    /**
     * Indicate the user is on an active trial.
     */
    public function onTrial(int $days = 5): static
    {
        return $this->state(fn() => [
            'trial_ends_at' => now()->addDays($days),
        ]);
    }
}
