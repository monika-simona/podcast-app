<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;
use App\Http\Controllers\Api\PodcastController;
use App\Http\Controllers\Api\EpisodeController;
use App\Http\Controllers\AuthController;

Route::post('register', [AuthController::class, 'register']);
Route::post('login', [AuthController::class, 'login']);

// Dobijanje trenutnog korisnika
Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

// Upravljanje sopstvenim nalogom - update i delete
Route::middleware('auth:sanctum')->group(function () {
    Route::put('users/{id}', [UserController::class, 'update'])->middleware('can:update-user,id');
    Route::delete('users/{id}', [UserController::class, 'destroy'])->middleware('can:delete-user,id');
});

// Rute dostupne svima
Route::get('podcasts', [PodcastController::class, 'index']);
Route::get('podcast/{id}', [PodcastController::class, 'show']);
Route::get('episodes', [EpisodeController::class, 'index']);
Route::get('episodes/{id}', [EpisodeController::class, 'show']);
//ruta za ispis svih epizaoda podkasta
Route::get('podcasts/{id}/episodes', [PodcastController::class, 'episodes']);

// Rute za autore - samo oni sa ulogom 'author' mogu da pristupe
Route::middleware(['auth:sanctum', 'role:author'])->group(function () {
    //Podkast
    Route::post('podcast', [PodcastController::class, 'store']);
    Route::put('podcast/{id}', [PodcastController::class, 'update'])->middleware('can:update-podcast,id');
    Route::delete('podcast/{id}', [PodcastController::class, 'destroy'])->middleware('can:delete-podcast,id');

    //Epizode
    Route::post('episodes', [EpisodeController::class, 'store'])->middleware('can:create-episode');
    Route::put('episodes/{id}', [EpisodeController::class, 'update'])->middleware('can:update-episode,id');
    Route::delete('episodes/{id}', [EpisodeController::class, 'destroy'])->middleware('can:delete-episode,id');
});

// Rute za admina - može da vidi sve korisnike i da ih briše
Route::middleware(['auth:sanctum', 'role:admin'])->group(function () {
    Route::apiResource('users', UserController::class)->only(['index', 'destroy']);
});

// Logout ruta, dostupna samo autentifikovanim korisnicima
Route::middleware('auth:sanctum')->post('logout', [AuthController::class, 'logout']);
