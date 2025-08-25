<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Episode;
use Illuminate\Http\Request;
use App\Models\Podcast;
use getID3;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;


class EpisodeController extends Controller
{
    public function index(Request $request)
    {
        $query = Episode::with(['podcast', 'tags']);

        if ($request->filled('title')) {
            $query->where('title', 'like', '%' . $request->title . '%');
        }

        if ($request->filled('tag_id')) {
            $query->whereHas('tags', function ($q) use ($request) {
                $q->where('tags.id', $request->tag_id);
            });
        }

        $sortBy = $request->get('sort_by', 'created_at');
        $sortOrder = $request->get('sort_order', 'desc');
        $query->orderBy($sortBy, $sortOrder);

        $perPage = $request->get('per_page', 5);
        $episodes = $query->paginate($perPage);

        return response()->json($episodes);
    }


    public function store(Request $request){
        if (auth()->user()->role !== 'author' && auth()->user()->role !== 'admin') {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $validated = $request->validate([
            'podcast_id'=> 'required|exists:podcasts,id',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'release_date' => 'nullable|date',
            'audio' => 'required|mimes:mp3,wav|max:40960'
        ]);

        $podcast = Podcast::findOrFail($validated['podcast_id']);
        if (auth()->user()->role !== 'admin' && auth()->id() !== $podcast->user_id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $path = $request->file('audio')->store('podcasts', 'public');

        $getID3 = new getID3;
        $fileInfo = $getID3->analyze(storage_path('app/public/' . $path));
        $duration = isset($fileInfo['playtime_seconds']) ? (int) ceil($fileInfo['playtime_seconds'] / 60) : null;

        $episode = Episode::create([
            'podcast_id' => $validated['podcast_id'],
            'title' => $validated['title'],
            'description' => $validated['description'] ?? null,
            'duration' => $duration,
            'release_date' => $validated['release_date'],
            'audio_path' => $path
        ]);

        // Obrisati keš za sve epizode i taj podkast
        Cache::forget('episodes_' . md5('podcast_id=' . $validated['podcast_id']));

        return response()->json($episode, 201);
    }

    public function show($id)
    {
        $cacheKey = 'episode_' . $id;
        $episode = Cache::remember($cacheKey, 60, function() use ($id) {
            return Episode::with('podcast')->findOrFail($id);
        });

        return response()->json($episode);
    }

    public function update(Request $request, $id)
    {
        $episode = Episode::findOrFail($id);

        if (auth()->user()->role !== 'admin' && auth()->id() !== $episode->podcast->user_id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $validated = $request->validate([
            'title' => 'sometimes|required|string|max:255',
            'description' => 'nullable|string',
            'release_date' => 'nullable|date',
            'audio' => 'sometimes|required|mimes:mp3,wav|max:40960'
        ]);

        if($request->hasFile('audio')){
            if($episode->audio_path){
                \Storage::disk('public')->delete($episode->audio_path);
            }
            $path = $request->file('audio')->store('podcasts', 'public');
            $getID3 = new getID3;
            $fileInfo = $getID3->analyze(storage_path('app/public/' . $path));
            $duration = isset($fileInfo['playtime_seconds']) ? (int) ceil($fileInfo['playtime_seconds'] / 60) : null;
            $validated['audio_path'] = $path;
            $validated['duration'] = $duration;
        }

        $episode->update($validated);

        // Obrisati keš za ovu epizodu i listu epizoda
        Cache::forget('episode_' . $id);
        Cache::forget('episodes_' . md5('podcast_id=' . $episode->podcast_id));

        return response()->json($episode);
    }

    public function destroy($id)
    {
        $episode = Episode::findOrFail($id);

        if (auth()->user()->role !== 'admin' && auth()->id() !== $episode->podcast->user_id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        if ($episode->audio_path && \Storage::disk('public')->exists($episode->audio_path)) {
            \Storage::disk('public')->delete($episode->audio_path);
        }

        $episode->delete();

        // Obrisati keš
        Cache::forget('episode_' . $id);
        Cache::forget('episodes_' . md5('podcast_id=' . $episode->podcast_id));

        return response()->json(null, 204);
    }

    public function play($id)
    {
        $episode = Episode::findOrFail($id);

        if (!auth()->check()) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }


        //povecaj broj preslusavanja
        $episode->increment('play_count');

        $filePath = storage_path('app/public/' . $episode->audio_path);
        if (!file_exists($filePath)) {
            return response()->json(['error' => 'Audio file not found'], 404);
        }

        $extension = pathinfo($filePath, PATHINFO_EXTENSION);
        $mimeType = match(strtolower($extension)) {
            'mp3' => 'audio/mpeg',
            'wav' => 'audio/wav',
            default => 'application/octet-stream',
        };

        return response()->stream(function() use ($filePath) {
            readfile($filePath);
        }, 200, [
            'Content-Type' => $mimeType,
            'Content-Disposition' => 'inline; filename="' . $episode->title . '.' . $extension . '"',
            'Content-Length' => filesize($filePath),
            'Accept-Ranges' => 'bytes'
        ]);
    }
    public function episodesByMonth()
    {
        $data = Episode::select(
            DB::raw("DATE_FORMAT(release_date, '%Y-%m') as month"),
            DB::raw('COUNT(*) as total')
        )
        ->groupBy('month')
        ->orderBy('month')
        ->get();

        return response()->json($data);
    }

    public function topEpisodes($limit = 10)
    {
        $episodes = Episode::with('podcast')
            ->orderByDesc('play_count')
            ->take($limit)
            ->get();

        return response()->json($episodes);
    }
}
