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

        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'audio' => 'required|mimes:mp3,wav|max:20480', // max 20MB
            'podcast_id' => 'required|exists:podcasts,id',
        ]);

        $path = $request->file('audio')->store('podcasts', 'public');

        $episode = Episode::create([
            'podcast_id' => $request->podcast_id,
            'title' => $request->title,
            'description' => $request->description,
            'duration' => $request->duration,
            'release_date' => $request->release_date,
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
        $episode->delete();

        return response()->json(null, 204);
    }


   public function episodes($id)
    {
        $podcast = Podcast::findOrFail($id);
        return response()->json($podcast->episodes);
    }

}
