<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use App\Models\Episode;

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

        // ako API vrati error, proveri response
        if (!$response->ok()) {
            return response()->json([
                'error' => 'Greška sa NewsAPI: ' . $response->body()
            ], 500);
        }

        $articles = $response->json()['articles'] ?? [];

        // Opcionalno: predloži epizode povezane sa query-jem
        $relatedEpisodes = Episode::where('title', 'like', "%{$query}%")
            ->orWhere('description', 'like', "%{$query}%")
            ->take(5)
            ->get();

        return response()->json([
            'news' => $articles,
            'related_episodes' => $relatedEpisodes
        ]);
    }
}