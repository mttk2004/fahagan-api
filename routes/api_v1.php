<?php

use App\Http\Controllers\Api\V1\AuthorController;
use App\Http\Controllers\Api\V1\BookController;
use App\Http\Controllers\Api\V1\DiscountController;
use App\Http\Controllers\Api\V1\GenreController;
use App\Http\Controllers\Api\V1\PublisherController;
use App\Http\Controllers\Api\V1\UserController;
use App\Http\Controllers\Api\V1\WhoAmI;


// Public-area
Route::apiResources([
	'books' => BookController::class,
	'authors' => AuthorController::class,
	'publishers' => PublisherController::class,
	'genres' => GenreController::class,
], ['only' => ['index', 'show']]);

// Authenticated-area
Route::middleware('auth:sanctum')->group(function() {
	Route::get('/whoami', [WhoAmI::class, 'whoAmI']);

	Route::apiResources([
		'books' => BookController::class,
		'authors' => AuthorController::class,
		'publishers' => PublisherController::class,
		'genres' => GenreController::class,
	], ['except' => ['index', 'show']]);

	Route::apiResource('users', UserController::class)
		 ->except('store');

	Route::apiResources([
		'discounts' => DiscountController::class,
	]);
});
