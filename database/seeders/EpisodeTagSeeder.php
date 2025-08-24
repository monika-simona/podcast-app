<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Episode;
use App\Models\Tag;

class EpisodeTagSeeder extends Seeder
{
    public function run()
    {
        $episodes = Episode::all();
        $tags = Tag::all();

        foreach ($episodes as $episode) {
            //random 2-5 tagova po epizodi
            $randomTags = $tags->random(rand(2, 5))->pluck('id')->toArray();
            $episode->tags()->syncWithoutDetaching($randomTags);
        }
    }
}
