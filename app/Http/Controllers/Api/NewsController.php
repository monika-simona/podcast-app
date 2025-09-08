<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use App\Models\Episode;
use App\Http\Resources\EpisodeResource;

class NewsController extends Controller
{
    public function search(Request $request)
    {
        $query = $request->query('query', 'technology');
        $category = $request->query('category');
        $apiKey = env('NEWSAPI_KEY');

        // Prešli smo na "everything" endpoint jer vraća više rezultata
        $url = 'https://newsapi.org/v2/everything';

        $params = [
            'apiKey' => $apiKey,
            'q' => $query,
            'pageSize' => 10,
            'language' => 'en', // možeš staviti 'sr' ali nema mnogo srpskih izvora
            'sortBy' => 'relevancy', // ili 'publishedAt'
        ];

        $response = Http::get($url, $params);

        if (!$response->ok()) {
            return response()->json([
                'error' => 'Greška sa NewsAPI: ' . $response->body()
            ], 500);
        }

        $articles = $response->json()['articles'] ?? [];

        // Dohvati povezane epizode sa eager-loaded podcast i transformiši kroz resource
        $relatedEpisodes = Episode::with('podcast')
            ->where('title', 'like', "%{$query}%")
            ->orWhere('description', 'like', "%{$query}%")
            ->take(5)
            ->get();

        return response()->json([
            'news' => $articles,
            'related_episodes' => EpisodeResource::collection($relatedEpisodes)
        ]);
    }
}
