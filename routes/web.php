<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return response()->json([
        'message' => 'Archive MVP API',
        'version' => '1.0.0',
    ]);
});
