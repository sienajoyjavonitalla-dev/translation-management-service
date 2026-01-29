<?php

use App\Http\Controllers\Api\LocaleController;
use App\Http\Controllers\Api\TagController;
use App\Http\Controllers\Api\TranslationController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::prefix('v1')->group(function () {
    // Health check endpoint
    Route::get('/health', function () {
        return response()->json([
            'status' => 'ok',
            'timestamp' => now()->toIso8601String(),
        ]);
    });

    // Locales routes (Phase 3)
    Route::apiResource('locales', LocaleController::class);

    // Tags routes (Phase 3)
    Route::apiResource('tags', TagController::class);

    // Translations routes (Phase 4)
    Route::apiResource('translations', TranslationController::class);

    // Public export endpoint (will be implemented in Phase 5)
    // Route::get('/export', [ExportController::class, 'index']);

    // Protected routes (will be implemented in Phase 6)
    // Route::middleware('auth:sanctum')->group(function () {
    //     // Translations routes (Phase 4)
    // });
});
