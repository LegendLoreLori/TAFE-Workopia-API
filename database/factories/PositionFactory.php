<?php

namespace Database\Factories;

use App\Models\Company;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Position>
 */
class PositionFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory()->state(['type' => 'Client']),
            'company_id' => function (array $attributes) {
                return User::find($attributes['user_id'])->company;
            },
            'start'=> $this->faker->dateTimeBetween('now')->format(\DateTimeInterface::ATOM),
            'end'=> $this->faker->dateTimeBetween('+1 month', '+3 months')->format(\DateTimeInterface::ATOM),
            'title'=> $this->faker->jobTitle(),
            'description'=> $this->faker->sentence(),
            'min_salary'=> $this->faker->numberBetween(40000,80000),
            'max_salary'=> $this->faker->numberBetween(90000,150000),
            'currency'=> $this->faker->currencyCode(),
            'benefits'=> $this->faker->words(5, true),
            'requirements'=> $this->faker->words(10, true),
            'type'=> $this->faker->randomElement(['Casual', 'Part-Time', 'Full-Time', 'Contract']),
        ];
    }
}
