<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ItemController;
use App\Http\Controllers\CollectionController;
use App\Http\Resources\UserResource;

// Public routes (no auth required)
Route::get('/items', [ItemController::class, 'index']);
Route::get('/items/{id}', [ItemController::class, 'show']);
Route::get('/collections', [CollectionController::class, 'index']);
Route::get('/collections/{id}', [CollectionController::class, 'show']);

// Protected routes (require Supabase JWT)
Route::middleware('supabase.auth')->group(function () {

    // Get authenticated user
    Route::get('/me', function (Request $request) {
        return new UserResource($request->user());
    });

    // Item routes
    Route::post('/items', [ItemController::class, 'store']);
    Route::put('/items/{id}', [ItemController::class, 'update']);
    Route::delete('/items/{id}', [ItemController::class, 'destroy']);

    // Collection routes
    Route::post('/collections', [CollectionController::class, 'store']);
    Route::put('/collections/{id}', [CollectionController::class, 'update']);
    Route::delete('/collections/{id}', [CollectionController::class, 'destroy']);

    // Collection-Item relationships
    Route::post('/collections/{id}/items', [CollectionController::class, 'addItem']);
    Route::delete('/collections/{id}/items/{itemId}', [CollectionController::class, 'removeItem']);
});
