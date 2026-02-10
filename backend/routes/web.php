<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\WidgetController;
use App\Http\Controllers\ApiController;

Route::get('/', [DashboardController::class, 'index']);
Route::get('/dashboard', [DashboardController::class, 'index']);
Route::get('/api/dashboard/data', [DashboardController::class, 'data']);

Route::get('/api/weather', [ApiController::class, 'weather']);
Route::get('/api/geolocation', [ApiController::class, 'geolocation']);

Route::middleware(['auth:sanctum'])->group(function () {
    Route::get('/widgets', [WidgetController::class, 'index']);
    Route::post('/widgets', [WidgetController::class, 'store']);
    Route::put('/widgets/{id}', [WidgetController::class, 'update']);
    Route::delete('/widgets/{id}', [WidgetController::class, 'destroy']);
    
    Route::get('/api/{provider}', [ApiController::class, 'fetch']);
});
