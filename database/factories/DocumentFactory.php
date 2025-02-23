<?php

namespace Database\Factories;

use App\Models\Document;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class DocumentFactory extends Factory
{
    protected $model = Document::class;

    public function definition(): array
    {
        return [
            'title' => $this->faker->sentence,
            'status' => $this->faker->randomElement(['draft', 'sent', 'signed']),
            'author_id' => User::factory(),
            'last_action' => $this->faker->randomElement(['created', 'updated', 'commented']),
            'last_action_by' => User::factory(),
        ];
    }
}
