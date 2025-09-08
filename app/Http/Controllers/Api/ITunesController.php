<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class ITunesController extends Controller
{
    public function search(Request $request)
    {
        $term = $request->query('term', 'podcast');
        $limit = $request->query('limit', 10);

        $response = Http::get('https://itunes.apple.com/search', [
            'term' => $term,
            'entity' => 'podcast',
            'limit' => $limit
        ]);

        $results = $response->json()['results'] ?? [];

        return response()->json($results);
    }
}
