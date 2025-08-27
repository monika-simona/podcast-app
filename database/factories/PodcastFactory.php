<?php

namespace Database\Factories;

use App\Models\Podcast;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class PodcastFactory extends Factory
{
    protected $model = Podcast::class;

    public function definition()
    {
        return [
            'title' => $this->faker->sentence(3, true),
            'description' => $this->faker->paragraph(),
            'user_id' => User::inRandomOrder()->first()->id ?? User::factory(),
            'cover_image' => 'images/default-cover.png',
        ];
    }
}
