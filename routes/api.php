<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;
use Illuminate\Http\Controllers\Api\PodcastController;
use Illuminate\Http\Controllers\Api\EpisodeController;

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});
//rute za korisnike
Route::get('users', [UserController::class, 'index']);
Route::post('users', [UserController::class, 'store']);
Route::get('users/{id}', [UserController::class, 'show']);
Route::put('users/{id}', [UserController::class, 'update']);
Route::delete('users/{id}', [UserController::class, 'destroy']);


// Rute dostupne svima
Route::get('podcasts', [PodcastController::class, 'index']);
Route::get('podcast/{id}', [PodcastController::class, 'show']);
Route::get('episodes', [EpisodeController::class, 'index']);
Route::get('episodes/{id}', [EpisodeController::class, 'show']);

// Rute koje zahtevaju autentifikaciju
Route::middleware(['auth:sanctum', 'role:admin,author'])->group(function () {
    // Podkast
    Route::post('podcast', [PodcastController::class, 'store']);
    Route::put('podcast/{id}', [PodcastController::class, 'update']);
    Route::delete('podcast/{id}', [PodcastController::class, 'destroy']);

    // Epizoda
    Route::post('episodes', [EpisodeController::class, 'store']);
    Route::put('episodes/{id}', [EpisodeController::class, 'update']);
    Route::delete('episodes/{id}', [EpisodeController::class, 'destroy']);
});