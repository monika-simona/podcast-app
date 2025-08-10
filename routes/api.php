<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;
use Illuminate\Http\Controllers\Api\PodcastController;
use Illuminate\Http\Controllers\Api\EpisodeController;
use App\Http\Controllers\AuthController;

Route::post('register', [AuthController::class, 'register']);
Route::post('login', [AuthController::class, 'login']);

// Rute dostupne svima
Route::get('podcasts', [PodcastController::class, 'index']);
Route::get('podcast/{id}', [PodcastController::class, 'show']);
Route::get('episodes', [EpisodeController::class, 'index']);
Route::get('episodes/{id}', [EpisodeController::class, 'show']);

// ruta za dobijanje svih epizoda na osnovu id-ja podkasat
Route::get('podcasts/{id}/episodes', [PodcastController::class, 'episodes']);


// Rute koje zahtevaju autentifikaciju
Route::middleware('auth:sanctum')->group(function () {
    // Logout ruta
    Route::post('logout', [AuthController::class, 'logout']);

    // Korisničke rute
    Route::apiResource('users', UserController::class);

    // Rute za kreiranje, izmenu i brisanje podkasta i epizoda dostupne samo adminu i author-u
    Route::middleware('role:admin,author')->group(function () {
        // Podkasti
        Route::post('podcast', [PodcastController::class, 'store']);
        Route::put('podcast/{id}', [PodcastController::class, 'update']);
        Route::delete('podcast/{id}', [PodcastController::class, 'destroy']);

        // Epizode
        Route::post('episodes', [EpisodeController::class, 'store']);
        Route::put('episodes/{id}', [EpisodeController::class, 'update']);
        Route::delete('episodes/{id}', [EpisodeController::class, 'destroy']);
    });
});
