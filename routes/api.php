<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\SearchController;
use App\Http\Controllers\DataController;
use App\Http\Controllers\DataSourceController;
use App\Http\Controllers\DataFeedController;

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

// Data sources endpoints
Route::prefix('data-sources')->group(function () {
    Route::get('/', [DataSourceController::class, 'index']);
    Route::post('/', [DataSourceController::class, 'store']);
    Route::post('/test', [DataSourceController::class, 'test']);
    Route::get('/{id}', [DataSourceController::class, 'show']);
    Route::put('/{id}', [DataSourceController::class, 'update']);
    Route::delete('/{id}', [DataSourceController::class, 'destroy']);
    Route::post('/{id}/refresh', [DataSourceController::class, 'refresh']);
    Route::post('/{id}/toggle', [DataSourceController::class, 'toggle']);
    Route::get('/{id}/data', [DataSourceController::class, 'getData']);
    Route::get('/{id}/preview', [DataSourceController::class, 'preview']);
});

// Data feed endpoints (combined data from all sources)
Route::prefix('feed')->group(function () {
    Route::get('/', [DataFeedController::class, 'feed']);
    Route::get('/stats', [DataFeedController::class, 'stats']);
});
