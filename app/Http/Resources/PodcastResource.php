<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PodcastResource extends JsonResource
{
    
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'description' => $this->description,
            'author' => $this->user?->name ?? 'Nepoznat',
            'user_id' => $this->user_id,
            'cover_image_url' => $this->cover_image
                ? asset('storage/' . $this->cover_image)
                : asset('images/default-cover.png'),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
