<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\MovieController;
use App\Http\Controllers\SeriesController;

// Routes for Movies
Route::prefix('movies')->group(function () {
    Route::get('/', [MovieController::class, 'index']); 
    Route::post('/', [MovieController::class, 'store']); 
    Route::get('/{id}', [MovieController::class, 'show']); 
    Route::put('/{id}', [MovieController::class, 'update']); 
    Route::delete('/{id}', [MovieController::class, 'destroy']); 
});

// Routes for Series
Route::prefix('series')->group(function () {
    Route::get('/', [SeriesController::class, 'index']); 
    Route::post('/', [SeriesController::class, 'store']); 
    Route::get('/{id}', [SeriesController::class, 'show']); 
    Route::put('/{id}', [SeriesController::class, 'update']); 
    Route::delete('/{id}', [SeriesController::class, 'destroy']); 
});
