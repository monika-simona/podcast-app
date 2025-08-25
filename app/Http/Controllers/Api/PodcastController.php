<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Podcast;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class PodcastController extends Controller
{
    public function index(Request $request)
    {
        $cacheKey = 'podcasts_' . md5($request->fullUrl());
        $perPage = $request->query('per_page', 10);

        $podcasts = Cache::remember($cacheKey, 60, function() use ($request, $perPage) {
            $query = Podcast::with('user');

            if ($request->has('title')) {
                $query->where('title', 'like', '%' . $request->query('title') . '%');
            }
            if ($request->has('user_name')) {
                $query->whereHas('user', function($q) use ($request) {
                    $q->where('name', 'like', '%' . $request->query('user_name') . '%');
                });
            }

            $paginated = $query->paginate($perPage);

            $paginated->getCollection()->transform(function ($podcast) {
                return [
                    'id' => $podcast->id,
                    'title' => $podcast->title,
                    'description' => $podcast->description,
                    'author' => $podcast->user->name ?? 'Nepoznat',
                    'user_id' => $podcast->user_id,
                ];
            });

            return $paginated;
        });

        return response()->json($podcasts);
    }

    public function store(Request $request)
    {
        if (auth()->user()->role !== 'author' && auth()->user()->role !== 'admin') {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);

        $podcast = Podcast::create([
            'title' => $validated['title'],
            'description' => $validated['description'] ?? null,
            'user_id' => auth()->id(),
        ]);

        // Obrisati keš liste podkasta
        Cache::flush();

        return response()->json($podcast,201);
    }

    public function show($id)
    {
        $cacheKey = 'podcast_show_' . $id;

        $podcast = Cache::remember($cacheKey, 60, function() use ($id) {
            $p = Podcast::with('user')->findOrFail($id);
            return [
                'id' => $p->id,
                'title' => $p->title,
                'description' => $p->description,
                'author' => $p->user->name ?? 'Nepoznat',
                'user_id' => $p->user_id,
            ];
        });

        return response()->json($podcast);
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
        ]);

        $podcast->update($validated);

        // Obrisati keš
        Cache::forget('podcast_show_' . $id);
        Cache::flush();

        return response()->json($podcast);
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

        $episodes = Cache::remember($cacheKey, 60, function() use ($id) {
            $podcast = Podcast::findOrFail($id);
            return $podcast->episodes;
        });

        return response()->json($episodes);
    }

    public function myPodcasts()
    {
        $userId = auth()->id();
        $cacheKey = 'my_podcasts_' . $userId;

        $podcasts = Cache::remember($cacheKey, 60, function() use ($userId) {
            return Podcast::where('user_id', $userId)->get();
        });

        return response()->json($podcasts);
    }

    public function topPodcasts()
    {
        // join epizoda i podkast, grupiši po podkastu i saberi play_count
        $podcasts = DB::table('podcasts')
            ->join('episodes', 'episodes.podcast_id', '=', 'podcasts.id')
            ->select('podcasts.id', 'podcasts.title', DB::raw('SUM(episodes.play_count) as total_plays'))
            ->groupBy('podcasts.id', 'podcasts.title')
            ->orderByDesc('total_plays')
            ->limit(10)
            ->get();

        return response()->json($podcasts);
    }
}
