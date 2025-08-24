<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Tag;

class TagSeeder extends Seeder
{
    public function run(): void
    {
        $tags = [
            'Technology', 'Education', 'Music', 'Gaming', 
            'Health', 'Science', 'Comedy', 'Business', 
            'News', 'Lifestyle'
        ];

        foreach ($tags as $tag) {
            Tag::firstOrCreate(['name' => $tag]);
        }
    }
}
