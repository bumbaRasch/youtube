<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

use App\Http\Controllers\ExtractController;

Route::post('/api/extract', ExtractController::class);
