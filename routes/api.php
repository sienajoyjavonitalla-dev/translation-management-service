<?php

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

    // Public export endpoint (will be implemented in Phase 5)
    // Route::get('/export', [ExportController::class, 'index']);

    // Protected routes (will be implemented in Phase 6)
    // Route::middleware('auth:sanctum')->group(function () {
    //     // Locales routes (Phase 3)
    //     // Tags routes (Phase 3)
    //     // Translations routes (Phase 4)
    // });
});
