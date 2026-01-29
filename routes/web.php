<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return response()->json([
        'message' => 'Translation Management Service API',
        'version' => '1.0.0',
        'documentation' => '/api/documentation',
    ]);
});
