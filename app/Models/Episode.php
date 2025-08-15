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

    //puna lokalna putanja do fajla
    public function getAudioPathAttribute($value)
    {
        return storage_path('app/public/' . $value);
    }

    //url za frontend
    public function getAudioUrlAttribute()
    {
        return asset('storage/' . $this->attributes['audio_path']);
    }
}
