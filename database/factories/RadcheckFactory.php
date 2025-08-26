<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Services\SshaHashService;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Radcheck>
 */
class RadcheckFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'username' => $this->faker->userName,
            'attribute' => 'SSHA-Password',
            'op' => ':=',
            'value' => SshaHashService::hash('password'),
            'email' => $this->faker->unique()->safeEmail,
            'recovery_token' => null,
            'token_expires' => null,
        ];
    }
}
