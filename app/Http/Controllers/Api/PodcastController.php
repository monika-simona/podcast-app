<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Podcast;
use Illuminate\Http\Request;

class PodcastController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Podcast::query();

        //filtriranje po naslovu
        if ($request->has('title')) {
            $query->where('title', 'like', '%' . $request->query('title') . '%');
        }

        // filtriranje po korisniku
        if ($request->has('user_name')) {
            $query->whereHas('user', function($q) use ($request) {
                $q->where('name', 'like', '%' . $request->query('user_name') . '%');
            });
        }

        //paginacija - broj zapisa po strani(10)
        $perPage = $request->query('per_page', 10);


        $podcasts = $query->paginate($perPage);
        return response()->json($podcasts);
    }

    /**
     * Store a newly created resource in storage.
     */
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
            'user_id' => auth()->id(), // vlasnik je prijavljeni korisnik
        ]);

        return response()->json($podcast,201);

    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $podcast = Podcast::findOrFail($id);
        return response()->json($podcast);
    }

    /**
     * Update the specified resource in storage.
     */
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

        return response()->json($podcast);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $podcast = Podcast::findOrFail($id);
        
        if (auth()->user()->role !== 'admin' && auth()->id() !== $podcast->user_id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }
        
        $podcast->delete();

        return response()->json(null, 204);

    }

    public function episodes($id)
    {
        $podcast = Podcast::findOrFail($id);
        $episodes = $podcast->episodes;
        return response()->json($episodes);
    }
}
