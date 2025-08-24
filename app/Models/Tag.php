<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Tag extends Model
{
    use HasFactory;

    protected $fillable = ['name'];

    public function episodes()
    {
        return $this->belongsToMany(Episode::class, 'episode_tag');
    }
}
