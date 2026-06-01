<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\InscripcionController;
use App\Http\Controllers\WompiWebhookController;

Route::post('/inscripciones', [InscripcionController::class, 'store']);

// Webhook de Wompi — sin CSRF ni autenticación
Route::post('/webhook/wompi', [WompiWebhookController::class, 'handle']);

Route::get('/health', function () {
    return response()->json(['status' => 'ok']);
});
