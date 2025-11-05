<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\SearchController;
use App\Http\Controllers\DataController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

// Search endpoints
Route::prefix('search')->group(function () {
    Route::post('/', [SearchController::class, 'search']);
    Route::get('/stats', [SearchController::class, 'stats']);
});

// Data management endpoints
Route::prefix('data')->group(function () {
    Route::get('/', [DataController::class, 'index']);
    Route::post('/', [DataController::class, 'store']);
    Route::post('/batch', [DataController::class, 'batchStore']);
    Route::post('/bulk-upload', [DataController::class, 'bulkUpload']);
    Route::post('/bulk-delete', [DataController::class, 'bulkDestroy']);
    Route::get('/folder-tree', [DataController::class, 'getFolderTree']);
    Route::get('/by-folder', [DataController::class, 'getByFolder']);
    Route::get('/{id}', [DataController::class, 'show']);
    Route::put('/{id}', [DataController::class, 'update']);
    Route::post('/{id}/move', [DataController::class, 'moveDocument']);
    Route::delete('/{id}', [DataController::class, 'destroy']);
});
