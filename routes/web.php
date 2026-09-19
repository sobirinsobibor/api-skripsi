<?php

use App\Http\Controllers\Api\UndergraduateThesisController;
use Illuminate\Support\Facades\Route;

Route::prefix('undergraduate-theses')->group(function () {
    Route::get('/', [UndergraduateThesisController::class, 'index']);    // Get All / Paginated
    Route::get('/{id}', [UndergraduateThesisController::class, 'show']); // Detail by ID
});