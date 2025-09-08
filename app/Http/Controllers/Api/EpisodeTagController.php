<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Episode;
use App\Models\Tag;
use Illuminate\Http\Request;
use App\Http\Resources\TagResource;

class EpisodeTagController extends Controller
{
    public function getTags($id)
    {
        $episode = Episode::with('tags')->findOrFail($id);
        return TagResource::collection($episode->tags);
    }

    public function attachTags(Request $request, $id)
    {
        $episode = Episode::findOrFail($id);

        $request->validate([
            'tags' => 'required|array',
            'tags.*' => 'string|max:50',
        ]);

        $tagIds = [];
        foreach ($request->tags as $tagName) {
            $tag = Tag::firstOrCreate(['name' => $tagName]);
            $tagIds[] = $tag->id;
        }

        $episode->tags()->syncWithoutDetaching($tagIds);

        return response()->json([
            'message' => 'Tagovi uspešno dodati',
            'tags' => TagResource::collection($episode->tags),
        ]);
    }

    public function detachTag($id, $tagId)
    {
        $episode = Episode::findOrFail($id);
        $episode->tags()->detach($tagId);

        return response()->json(['message' => 'Tag uklonjen']);
    }
}
