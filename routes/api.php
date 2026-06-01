<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\InscripcionController;
use App\Http\Controllers\WompiWebhookController;

use App\Http\Controllers\AdminController;

Route::post('/inscripciones', [InscripcionController::class, 'store']);
Route::post('/webhook/wompi', [WompiWebhookController::class, 'handle']);
Route::get('/admin/inscripciones', [AdminController::class, 'index']);

Route::get('/health', function () {
    return response()->json(['status' => 'ok']);
});
