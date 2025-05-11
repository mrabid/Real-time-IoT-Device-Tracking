<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DeviceController; // Add this line at the top

Route::get('/', function () {
    return view('welcome');
});

// Add these new routes below the existing ones
Route::post('/api/data', [DeviceController::class, 'store']);
Route::get('/api/data', [DeviceController::class, 'index']);
Route::get('/dashboard', [DeviceController::class, 'dashboard']);