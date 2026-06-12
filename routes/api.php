<?php

use App\Http\Controllers\Api\BukuApiController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

// Buku Analytics API - Increment View Count
// Throttle: Max 10 requests per minute per IP to prevent spam/manipulation
Route::post('/buku/{id}/increment-view', [BukuApiController::class, 'incrementView'])
    ->middleware('throttle:10,1')
    ->name('api.buku.increment-view');
