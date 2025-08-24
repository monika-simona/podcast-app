<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Episode extends Model
{
    use HasFactory;

    protected $fillable = ['podcast_id','title','description', 'duration', 'release_date', 'audio_path'];
    public function podcast(){
        return $this->belongsTo(Podcast::class);
    }


    //url za frontend
    public function getAudioUrlAttribute()
    {
        return $this->audio_path 
        ? asset('storage/' . $this->audio_path) 
        : null;
    }

    public function tags()
    {
        return $this->belongsToMany(Tag::class, 'episode_tag');
    }
}
