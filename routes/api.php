<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ExtractController;

Route::post('/extract', ExtractController::class);
