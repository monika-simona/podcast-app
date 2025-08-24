<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Episode;
use App\Models\Tag;
use Illuminate\Http\Request;

class EpisodeTagController extends Controller
{
    //dobavljanje svih tagova za epizodu
    public function getTags($id)
    {
        $episode = Episode::with('tags')->findOrFail($id);
        return response()->json($episode->tags);
    }

    //dodavanje tagova epizodi
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
            'tags' => $episode->tags
        ]);
    }

    //uklanjanje taga iz epizode
    public function detachTag($id, $tagId)
    {
        $episode = Episode::findOrFail($id);
        $episode->tags()->detach($tagId);

        return response()->json(['message' => 'Tag uklonjen']);
    }
}
