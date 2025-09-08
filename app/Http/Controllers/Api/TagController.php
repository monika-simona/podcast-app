<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Tag;
use Illuminate\Http\Request;
use App\Http\Resources\TagResource;
use App\Http\Resources\EpisodeResource;

class TagController extends Controller
{
    public function index()
    {
        return TagResource::collection(Tag::withCount('episodes')->get());
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:50|unique:tags,name',
        ]);

        $tag = Tag::create($validated);

        return new TagResource($tag);
    }

    public function destroy($id)
    {
        $tag = Tag::findOrFail($id);
        $tag->delete();

        return response()->json(null, 204);
    }

    public function getEpisodes(Request $request, $id)
    {
        $perPage = $request->query('per_page', 10);
        $tag = Tag::findOrFail($id);

        $query = $tag->episodes()->with('podcast');

        if ($request->filled('title')) {
            $query->where('title', 'like', '%' . $request->query('title') . '%');
        }

        $sortBy = $request->query('sort_by', 'created_at');
        $sortOrder = $request->query('sort_order', 'desc');
        $allowedSorts = ['id', 'title', 'created_at', 'updated_at', 'release_date'];

        if (in_array($sortBy, $allowedSorts)) {
            $query->orderBy($sortBy, $sortOrder);
        }

        $episodes = $query->paginate($perPage);

        return EpisodeResource::collection($episodes);
    }
}
