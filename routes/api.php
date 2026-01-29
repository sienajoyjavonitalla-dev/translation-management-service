<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\LocaleController;
use App\Http\Controllers\Api\TagController;
use App\Http\Controllers\Api\TranslationController;
use App\Http\Controllers\Api\ExportController;
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

    // Public export endpoint (Phase 5)
    Route::get('/export', [ExportController::class, 'index']);

    // Auth (Phase 6)
    Route::post('/auth/login', [AuthController::class, 'login']);

    // Protected routes (Phase 6)
    Route::middleware(['auth:sanctum', 'throttle:60,1'])->group(function () {
        Route::post('/auth/logout', [AuthController::class, 'logout']);

        // Locales routes (Phase 3)
        Route::apiResource('locales', LocaleController::class);

        // Tags routes (Phase 3)
        Route::apiResource('tags', TagController::class);

        // Translations routes (Phase 4)
        Route::apiResource('translations', TranslationController::class);
    });
});
