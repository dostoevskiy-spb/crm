<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/ping', function () {
    return response()->json([
        'message' => 'pong',
        'timestamp' => now()->toISOString(),
        'status' => 'success'
    ]);
});

Route::post('/ping', function (Request $request) {
    return response()->json([
        'message' => 'pong',
        'received_data' => $request->all(),
        'timestamp' => now()->toISOString(),
        'status' => 'success'
    ]);
});
