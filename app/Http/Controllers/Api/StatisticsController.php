<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;

class StatisticsController extends Controller
{
    public function episodesByTag()
    {
        $data = DB::table('tags')
            ->join('episode_tag', 'tags.id', '=', 'episode_tag.tag_id')
            ->join('episodes', 'episodes.id', '=', 'episode_tag.episode_id')
            ->select('tags.name', DB::raw('COUNT(episodes.id) as total_episodes'))
            ->groupBy('tags.name')
            ->orderByDesc('total_episodes')
            ->get();

        return response()->json($data);
    }
}
