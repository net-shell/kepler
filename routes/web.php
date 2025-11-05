<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

Route::get('/document/{id}', function ($id) {
    return Inertia::render('DocumentShow', ['id' => $id]);
})->name('document.show');

Route::get('/{any}', function () {
    return Inertia::render('Dashboard');
})->where('any', '.*');
