<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Podcast;
use App\Http\Resources\PodcastResource;
use App\Http\Resources\EpisodeResource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class PodcastController extends Controller
{
    public function index(Request $request)
    {
        $cacheKey = 'podcasts_' . md5($request->fullUrl());
        $perPage = $request->query('per_page', 10);

        $podcasts = Cache::remember($cacheKey, 60, function () use ($request, $perPage) {
            $query = Podcast::with('user');

            if ($request->has('title')) {
                $query->where('title', 'like', '%' . $request->query('title') . '%');
            }
            if ($request->has('user_name')) {
                $query->whereHas('user', function ($q) use ($request) {
                    $q->where('name', 'like', '%' . $request->query('user_name') . '%');
                });
            }

            return $query->paginate($perPage);
        });

        return PodcastResource::collection($podcasts);
    }

    public function store(Request $request)
    {
        if (auth()->user()->role !== 'author' && auth()->user()->role !== 'admin') {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'cover_image' => 'nullable|image|mimes:jpg,jpeg,png|max:2048'
        ]);

        if ($request->hasFile('cover_image')) {
            $path = $request->file('cover_image')->store('covers', 'public');
            $validated['cover_image'] = $path;
        }

        $podcast = Podcast::create([
            'title' => $validated['title'],
            'description' => $validated['description'] ?? null,
            'cover_image' => $validated['cover_image'] ?? null,
            'user_id' => auth()->id(),
        ]);

        Cache::flush();

        return new PodcastResource($podcast->load('user'));
    }

    public function show($id)
    {
        $cacheKey = 'podcast_show_' . $id;

        $podcast = Cache::remember($cacheKey, 60, function () use ($id) {
            return Podcast::with('user')->findOrFail($id);
        });

        return new PodcastResource($podcast);
    }

    public function update(Request $request, $id)
    {
        $podcast = Podcast::findOrFail($id);

        if (auth()->user()->role !== 'admin' && auth()->id() !== $podcast->user_id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $validated = $request->validate([
            'title' => 'sometimes|required|string|max:255',
            'description' => 'nullable|string',
            'cover_image' => 'nullable|image|mimes:jpg,jpeg,png|max:2048'
        ]);

        if ($request->hasFile('cover_image')) {
            if ($podcast->cover_image && Storage::disk('public')->exists($podcast->cover_image)) {
                Storage::disk('public')->delete($podcast->cover_image);
            }
            $path = $request->file('cover_image')->store('covers', 'public');
            $validated['cover_image'] = $path;
        }

        $podcast->update($validated);

        Cache::forget('podcast_show_' . $id);
        Cache::flush();

        return new PodcastResource($podcast->load('user'));
    }

    public function destroy($id)
    {
        $podcast = Podcast::findOrFail($id);

        if (auth()->user()->role !== 'admin' && auth()->id() !== $podcast->user_id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $podcast->delete();

        Cache::forget('podcast_show_' . $id);
        Cache::flush();

        return response()->json(null, 204);
    }

    public function episodes($id)
    {
        $cacheKey = 'podcast_episodes_' . $id;

        $episodes = Cache::remember($cacheKey, 60, function () use ($id) {
            $podcast = Podcast::with('episodes')->findOrFail($id);
            return $podcast->episodes;
        });

        return EpisodeResource::collection($episodes);
    }

    public function myPodcasts()
    {
        $userId = auth()->id();
        $cacheKey = 'my_podcasts_' . $userId;

        $podcasts = Cache::remember($cacheKey, 60, function () use ($userId) {
            return Podcast::where('user_id', $userId)->with('user')->get();
        });

        return PodcastResource::collection($podcasts);
    }

    public function topPodcasts()
    {
        $podcasts = DB::table('podcasts')
            ->join('episodes', 'episodes.podcast_id', '=', 'podcasts.id')
            ->select(
                'podcasts.id',
                'podcasts.title',
                'podcasts.cover_image',
                DB::raw('SUM(episodes.play_count) as total_plays')
            )
            ->groupBy('podcasts.id', 'podcasts.title', 'podcasts.cover_image')
            ->orderByDesc('total_plays')
            ->limit(10)
            ->get();

        $podcasts->transform(function ($podcast) {
            return [
                'id' => $podcast->id,
                'title' => $podcast->title,
                'cover_image_url' => $podcast->cover_image
                    ? asset('storage/' . $podcast->cover_image)
                    : asset('images/default-cover.png'),
                'total_plays' => $podcast->total_plays,
            ];
        });

        return response()->json($podcasts);
    }
}
