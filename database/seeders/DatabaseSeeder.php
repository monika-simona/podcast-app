<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Podcast;
use App\Models\Episode;


class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        //KREIRANJE 5 KORISNIKA PO ULOGAMA
        User::factory()->count(2)->admin()->create();
        User::factory()->count(3)->author()->create();
        User::factory()->count(10)->user()->create();


        // KREIRANJE PODKASTA ZA AUTORE
        Podcast::factory()->count(20)->create();


        //KREIRANJE EPIZODE ZA PODKSATE
        Podcast::all()->each(function ($podcast) {
            Episode::factory()->count(rand(1, 5))->create([
                'podcast_id' => $podcast->id,
            ]);
        });

        
    }
}
