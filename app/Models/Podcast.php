<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;


class Podcast extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'description', 
        'user_id',
        'cover_image'
    ];
    public function user(){

    return $this->belongsTo(User::class);
    }

    public function episodes(){
        return $this->hasMany(Episode::class);
    }

    protected $appends = ['cover_image_url'];

    public function getCoverImageUrlAttribute() {
        return $this->cover_image 
            ? Storage::url($this->cover_image) 
            : null;
    }
}
