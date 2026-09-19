<?php

use App\Http\Controllers\Api\UndergraduateThesisController;
use Illuminate\Support\Facades\Route;

Route::prefix('theses')->group(function () {
    Route::get('/', [UndergraduateThesisController::class, 'index']);
    Route::get('/stats', [UndergraduateThesisController::class, 'stats']);
    Route::get('/years', [UndergraduateThesisController::class, 'years']);
    Route::get('/programs', [UndergraduateThesisController::class, 'programs']);
    Route::get('/{id}', [UndergraduateThesisController::class, 'show']);
});

Route::get('/health', function () {
    return response()->json([
        'status' => 'ok',
        'message' => 'API is running',
        'timestamp' => now()->toISOString(),
    ]);
});