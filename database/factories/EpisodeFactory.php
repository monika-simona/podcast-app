<?php

namespace Database\Factories;

use App\Models\Episode;
use App\Models\Podcast;
use Illuminate\Database\Eloquent\Factories\Factory;

class EpisodeFactory extends Factory
{
    protected $model = Episode::class;

    public function definition()
    {

        $dummyAudioPath = 'podcasts/podkast poroba1.mp3';


        return [
            'podcast_id' => Podcast::inRandomOrder()->first()->id ?? Podcast::factory(),
            'title' => $this->faker->sentence(4, true),
            'description' => $this->faker->paragraph(),
            'duration' => $this->faker->numberBetween(5, 120),
            'release_date' => $this->faker->date(),
            'audio_path' => $dummyAudioPath, // dummy audio fajl
        ];
    }
}
