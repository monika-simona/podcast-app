<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Episode;
use Illuminate\Http\Request;
use App\Models\Podcast;
use getID3;


class EpisodeController extends Controller
{
    public function index(Request $request)
    {
        $query = Episode::with('podcast');

        //filtriranje po nazivu epizode
        if ($request->has('title')) {
            $query->where('title', 'like', '%' . $request->query('title') . '%');
        }

        //filtriranje poreko id
        if ($request->has('podcast_id')) {
            $query->where('podcast_id', $request->query('podcast_id'));
        }

        // filtriranje po nazivu podkasta
        if ($request->has('podcast_title')) {
            $query->whereHas('podcast', function($q) use ($request) {
                $q->where('title', 'like', '%' . $request->query('podcast_title') . '%');
            });
        }

        //filtriranje po korisniku
        if ($request->has('user_name')) {
            $query->whereHas('podcast.user', function($q) use ($request) {
                $q->where('username', 'like', '%' . $request->query('user_name') . '%');
            });
        }

        $perPage = $request->query('per_page', 10);
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

        // samo vlasnik podcasta ili admin može dodati epizodu
        if (auth()->user()->role !== 'admin' && auth()->id() !== $podcast->user_id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $path = $request->file('audio')->store('podcasts', 'public');

        //getID3
        $getID3 = new getID3;
        $fileInfo = $getID3->analyze(storage_path('app/public/' . $path));
        $duration = null;
        if (isset($fileInfo['playtime_seconds'])) {
            $seconds = (int) $fileInfo['playtime_seconds'];
            $duration = (int) ceil($seconds / 60); // duration u minutima
        }

         $episode = Episode::create([
            'podcast_id' => $validated['podcast_id'],
            'title' => $validated['title'],
            'description' => $validated['description'] ?? null,
            'duration' => $duration,
            'release_date' => $validated['release_date'],
            'audio_path' => $path
        ]);

        return response()->json($episode, 201);

        
    }
    //prikaz jedne epizode
    public function show($id)
    {
        $episode = Episode::with('podcast')->findOrFail($id);
        return response()->json($episode);
    }

    //izmena epizode
    public function update(Request $request, $id)
    {
        $episode = Episode::findOrFail($id);

        if (auth()->user()->role !== 'admin' && auth()->id() !== $episode->podcast->user_id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        \Log::info('Raw request data:', $request->all());

        $validated = $request->validate([
            'title' => 'sometimes|required|string|max:255',
            'description' => 'nullable|string',
            'release_date' => 'nullable|date',
            'audio' => 'sometimes|required|mimes:mp3,wav|max:40960'
        ]);
        
        \Log::info('Validated data:', $validated);

        if($request->hasFile('audio')){
            if($episode->audio_path){
                \Storage::disk('public')->delete($episode->audio_path);
            }
            $path = $request->file('audio')->store('podcasts', 'public');
            
            
            $getID3 = new getID3;
            $fileInfo = $getID3->analyze(storage_path('app/public/' . $path));
            $duration = null;
            if (isset($fileInfo['playtime_seconds'])) {
                $seconds = (int) $fileInfo['playtime_seconds'];
                $duration = (int) ceil($seconds / 60); // duration u minutima
            }
            
            $validated['audio_path'] = $path;
            $validated['duration'] = $duration;
        }
        \Log::info('Validated data:', $validated);


        $episode->update($validated);

        return response()->json($episode);
    }

    public function destroy($id)
    {
        $episode = Episode::findOrFail($id);
        
        if (auth()->user()->role !== 'admin' && auth()->id() !== $episode->podcast->user_id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }
        
        // Brisanje audio fajla
        if ($episode->audio_path && \Storage::disk('public')->exists($episode->audio_path)) {
            \Storage::disk('public')->delete($episode->audio_path);
        }

        $episode->delete();

        return response()->json(null, 204);
    }

    //strimovanje audio fajla za registrovane (logovane) korisnike
    public function play($id)
    {
        $episode = Episode::findOrFail($id);

        if (!auth()->check()) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $filePath = storage_path('app/public/' . $episode->audio_path);

        if (!file_exists($filePath)) {
            return response()->json(['error' => 'Audio file not found'], 404);
        }
        
        //provera tipa fajla
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


}
