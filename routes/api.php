<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;
use App\Http\Controllers\Api\PodcastController;
use App\Http\Controllers\Api\EpisodeController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\Api\ITunesController;
use App\Http\Controllers\Api\NewsController;
use App\Http\Controllers\Api\EpisodeTagController;
use App\Http\Controllers\Api\TagController;
use App\Http\Controllers\Api\StatisticsController;


Route::post('register', [AuthController::class, 'register']);
Route::post('login', [AuthController::class, 'login']);

// dobijanje trenutnog korisnika
Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

// upravljanje sopstvenim nalogom - update i delete
Route::middleware('auth:sanctum')->group(function () {
    Route::put('users/{id}', [UserController::class, 'update']);
    Route::delete('users/{id}', [UserController::class, 'destroy']);
});

// rute dostupne svima
Route::get('podcasts', [PodcastController::class, 'index']);
Route::get('podcasts/{id}', [PodcastController::class, 'show']);
Route::get('episodes', [EpisodeController::class, 'index']);
Route::get('episodes/{id}', [EpisodeController::class, 'show']);
//ruta za ispis svih epizaoda podkasta
Route::get('podcasts/{id}/episodes', [PodcastController::class, 'episodes']);

//ruta za slusanje podkasta dozvoljena samo ulogovanim korisnicima
Route::middleware('auth:sanctum')->get('episodes/{id}/play', [EpisodeController::class, 'play']);

// rute za autore - samo oni sa ulogom 'author' mogu da pristupe
Route::middleware(['auth:sanctum', 'role:author,admin'])->group(function () {
    //Podkast
    Route::get('my-podcasts', [PodcastController::class, 'myPodcasts']);
    Route::post('podcasts', [PodcastController::class, 'store']);
    Route::put('podcasts/{id}', [PodcastController::class, 'update']);
    Route::delete('podcasts/{id}', [PodcastController::class, 'destroy']);

    //Epizode
    Route::post('episodes', [EpisodeController::class, 'store']);
    Route::put('episodes/{id}', [EpisodeController::class, 'update']);
    Route::delete('episodes/{id}', [EpisodeController::class, 'destroy']);
});

// rute za admina - može da vidi korisnike i da ih briše
Route::middleware(['auth:sanctum', 'role:admin'])->group(function () {
    // Resource rute
    Route::apiResource('users', UserController::class)->only(['index', 'store', 'destroy']);

});


// Logout ruta, dostupna samo autentifikovanim korisnicima
Route::middleware('auth:sanctum')->post('logout', [AuthController::class, 'logout']);

//javna ruta

Route::get('/itunes-search', [ITunesController::class, 'search']);

Route::get('/news', [NewsController::class, 'search']);

//rute za tagove dostupne svima
Route::get('/tags', [TagController::class, 'index']);   
Route::get('/episodes/{id}/tags', [EpisodeTagController::class, 'getTags']);  
Route::get('/tags/{id}/episodes', [TagController::class, 'getEpisodes']);


//rute za tagove ulogovanim korisnicima
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/tags', [TagController::class, 'store']); 
    Route::delete('/tags/{id}', [TagController::class, 'destroy']);
    Route::post('/episodes/{id}/tags', [EpisodeTagController::class, 'attachTags']);
    Route::delete('/episodes/{id}/tags/{tagId}', [EpisodeTagController::class, 'detachTag']);
});

//statistika
Route::get('/stats/episodes-by-tag', [StatisticsController::class, 'episodesByTag']); 
Route::get('/episodes-by-month', [EpisodeController::class, 'episodesByMonth']);
Route::get('/top-episodes', [EpisodeController::class, 'topEpisodes']);
Route::get('/top-podcasts', [PodcastController::class, 'topPodcasts']);