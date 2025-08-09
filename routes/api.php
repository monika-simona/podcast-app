<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Http\Controllers\Api\PodcastController;

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});
//ruta za prikaz svih podkastova
Route::get('podcasts',[PodcastController::class, 'index']);

//prikaz jednog podkasta preko id-ja
Route::get('podcast/{id}', [PodcastController::class, 'show']);

//kriranje novog podkasta
Route::post('podcast', [PodcastController::class, 'store']);


//(delimicna) izmena podkasta preko id-ja
Route::put('podcast/{id}', [PodcastController::class, 'update']);
Route::patch('podcast/{id}', [PodcastController::class, 'update']);


//brisanje podkasta preko id-ja
Route::delete('podcast/{id}', [PodcastController::class, 'destroy']);



