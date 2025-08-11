<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Episode;
use Illuminate\Http\Request;

class EpisodeController extends Controller
{
    public function index()
    {
        return Episode::with('podcast')->get();
    }
    
    public function store(Request $request){

        if (auth()->user()->role !== 'author' && auth()->user()->role !== 'admin') {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $validated = $request->validate([
            'podcast_id'=> 'required|exists:podcasts,id',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'duration' => 'required|integer|min:1',
            'release_date' => 'required|date',
            'audio' => 'required|mimes:mp3,wav|max:20480'
        ]);

        $podcast = Podcast::findOrFail($validated['podcast_id']);

        // samo vlasnik podcasta ili admin može dodati epizodu
        if (auth()->user()->role !== 'admin' && auth()->id() !== $podcast->user_id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $path = $request->file('audio')->store('podcasts', 'public');

        $episode = Episode::create([
            'podcast_id' => $validated['podcast_id'],
            'title' => $validated['title'],
            'description' => $validated['description'] ?? null,
            'duration' => $validated['duration'],
            'release_date' => $validated['release_date'],
            'audio_path' => $path
        ]);

        return response()->json($episode, 201);

        
    }
    //prikaz jedne epizode
    public function show($id)
    {
        return Episode::with('podcast')->findOrFail($id);
    }

    //izmena epizode
    public function update(Request $request, $id)
    {
        $episode = Episode::findOrFail($id);

        if (auth()->user()->role !== 'admin' && auth()->id() !== $episode->podcast->user_id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $validated = $request->validate([
            'title' => 'string|max:255',
            'description' => 'nullable|string',
            'duration' => 'nullable|integer',
            'release_date' => 'nullable|date',
            'audio' => 'nullable|mimes:mp3,wav|max:20480' 
        ]);

        if($request->hasFile('audio')){
            if($episode->audio_path){
                \Storage::disk('public')->delete($episode->audio_path);
            }
            $path = $request->file('audio')->store('podcasts', 'public');
            $validated['audio_path'] = $path;
        }

        $episode->update($validated);

        return response()->json($episode);
    }
    public function destroy($id)
    {
        $episode = Episode::findOrFail($id);
        
        if (auth()->user()->role !== 'admin' && auth()->id() !== $episode->podcast->user_id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }
        
        $episode->delete();

        return response()->json(null, 204);
    }


   public function episodes($id)
    {
        $podcast = Podcast::findOrFail($id);
        return response()->json($podcast->episodes);
    }

}
