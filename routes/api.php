<?php

use App\Http\Controllers\Api\AuthController;
use Illuminate\Support\Facades\Route;


Route::get('/', function() {
	return response()->json(['message' => 'Hello World!']);
});

Route::post('/login', [AuthController::class, 'login']);
