<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\JobTitle>
 */
class JobTitleFactory extends Factory
{
    public static $jobTitles = [
        'Management',
        'Logistics',
        'Social Integration',
        'Design',
        'Content',
        'Secretary',
        'Social Relations',
        'Treasury',
    ];

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->unique()->randomElement(self::$jobTitles),
        ];
    }
}
