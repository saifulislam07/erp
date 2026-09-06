<?php

namespace Database\Factories;

use App\Models\Client;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Client>
 */
class ClientFactory extends Factory
{
    protected $model = Client::class;

    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'phone' => fake()->numerify('018########'),
            'address' => fake()->address(),
            'business_name' => fake()->company(),
            'type' => 'client',
            // Cast to `hashed` on the model, so this is the plain sign-in password.
            'password' => 'password',
            'status' => true,
        ];
    }

    public function agent(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'agent',
        ]);
    }

    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => false,
        ]);
    }
}
