<?php

use App\Http\Controllers\Api\V1\UserController;


//Route::middleware('auth:sanctum')->group(function() {
	Route::apiResource('users', UserController::class)
		 ->except('store');
//});
