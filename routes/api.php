<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Http\Controllers\Api\PodcastController;
use Illuminate\Http\Controllers\Api\EpisodeController;

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});
//ruta za prikaz svih podkastova
Route::get('podcasts',[PodcastController::class, 'index']);

//prikaz jednog podkasta preko id-ja
Route::get('podcast/{id}', [PodcastController::class, 'show']);

//kriranje novog podkasta
Route::post('podcast', [PodcastController::class, 'store']);


//izmena podkasta preko id-ja
Route::put('podcast/{id}', [PodcastController::class, 'update']);

//brisanje podkasta preko id-ja
Route::delete('podcast/{id}', [PodcastController::class, 'destroy']);

//za epizode

Route::get('episodes', [EpisodeController::class, 'index']);

Route::post('episodes', [EpisodeController::class, 'store']);

Route::get('episodes/{id}', [EpisodeController::class, 'show']);

Route::put('episodes/{id}', [EpisodeController::class, 'update']);

Route::delete('episodes/{id}', [EpisodeController::class, 'destroy']);