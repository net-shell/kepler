<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

Route::get('/', function () {
    return Inertia::render('Landing');
})->name('landing');

Route::middleware(['auth'])->group(function () {
    Route::get('/dashboard', function () {
        return Inertia::render('Dashboard');
    })->name('dashboard');

    Route::get('/document/{id}', function ($id) {
        return Inertia::render('DocumentShow', ['id' => $id]);
    })->name('document.show');

    Route::get('/data-sources', function () {
        return Inertia::render('DataSources');
    })->name('data-sources');
});

Route::get('/{any}', function () {
    return Inertia::render('Landing');
})->where('any', '.*');
