<?php

use App\Http\Controllers\DistanceController;
use Illuminate\Support\Facades\Route;

Route::get('/distance', [DistanceController::class, 'distance']);
