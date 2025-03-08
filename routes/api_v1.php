<?php

use App\Http\Controllers\Api\V1\AuthorController;
use App\Http\Controllers\Api\V1\BookController;
use App\Http\Controllers\Api\V1\DiscountController;
use App\Http\Controllers\Api\V1\PublisherController;
use App\Http\Controllers\Api\V1\UserController;


//Route::middleware('auth:sanctum')->group(function() {
	Route::apiResource('users', UserController::class)
		 ->except('store');
//});

Route::apiResources([
	'books' => BookController::class,
	'authors' => AuthorController::class,
	'publishers' => PublisherController::class,
	'discounts' => DiscountController::class
]);
