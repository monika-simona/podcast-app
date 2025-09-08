<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class EpisodeResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id'           => $this->id,
            'title'        => $this->title,
            'description'  => $this->description,
            'duration'     => $this->duration,
            'release_date' => $this->release_date,
            'audio_url'    => $this->audio_url,
            'play_count'   => $this->play_count,
            'podcast'      => new PodcastResource($this->whenLoaded('podcast')),
            'tags'         => TagResource::collection($this->whenLoaded('tags')),
            'created_at'   => $this->created_at,
        ];
    }
}
