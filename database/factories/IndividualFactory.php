<?php

namespace Database\Factories;

use App\Domain\Individual\Models\Individual;
use App\Models\Individual;
use Illuminate\Database\Eloquent\Factories\Factory;

class IndividualFactory extends Factory
{
    protected $model = Individual::class;

    public function definition(): array
    {
        return [
            'first_name' => $this->faker->firstName(),
            'last_name' => $this->faker->lastName(),
            'middle_name' => $this->faker->firstName(), // Using firstName for middle name
            'position_id' => null, // Will be set when positions are created
            'status_id' => 1, // Default status, will be updated when statuses are created
            'login' => $this->faker->optional(0.3)->userName(), // 30% chance of having login
            'is_company_employee' => $this->faker->boolean(20), // 20% chance of being company employee
            'creator_id' => 1, // Will be updated when we have actual users
        ];
    }

    public function companyEmployee(): static
    {
        return $this->state(function (array $attributes) {
            return [
                'is_company_employee' => true,
                'login' => $this->faker->userName(),
            ];
        });
    }

    public function withLogin(): static
    {
        return $this->state(function (array $attributes) {
            return [
                'login' => $this->faker->userName(),
            ];
        });
    }

    public function lead(): static
    {
        return $this->state(function (array $attributes) {
            return [
                'status_id' => 1, // Lead status
                'is_company_employee' => false,
                'login' => null,
            ];
        });
    }
}
