<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\V1\UserController;
use Illuminate\Support\Facades\Route;


Route::get('/', function() {
	return response()->json(['message' => 'Hello World!']);
});

Route::post('/login', [AuthController::class, 'login']);
Route::post('/register', [AuthController::class, 'register']);

Route::middleware('auth.*')->group(function() {
	Route::post('/logout', [AuthController::class, 'logout']);
	Route::post('/change-password', [UserController::class, 'changePassword']);
});
