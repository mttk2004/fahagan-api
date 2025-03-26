<?php

use App\Http\Controllers\Api\V1\AddressController;
use App\Http\Controllers\Api\V1\AuthorController;
use App\Http\Controllers\Api\V1\BookController;
use App\Http\Controllers\Api\V1\CartItemController;
use App\Http\Controllers\Api\V1\DiscountController;
use App\Http\Controllers\Api\V1\GenreController;
use App\Http\Controllers\Api\V1\PublisherController;
use App\Http\Controllers\Api\V1\UserController;


// Public-area
Route::apiResources([
	'books' => BookController::class,
	'authors' => AuthorController::class,
	'publishers' => PublisherController::class,
	'genres' => GenreController::class,
], ['only' => ['index', 'show']]);

// Authenticated-area
Route::middleware('auth.*')->group(function() {
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

	// Customer only area
	Route::middleware('auth.customer')->group(function() {
		// Cart
		Route::get('cart', [CartItemController::class, 'index'])
			 ->name('cart.index');
		Route::post('cart/add', [CartItemController::class, 'addToCart'])
			 ->name('cart.add');
		Route::post('cart/update-quantity', [CartItemController::class, 'updateCartItemQuantity'])
			 ->name('cart.update-quantity');
		Route::delete(
			'cart/remove/{book_id}',
			[CartItemController::class, 'removeFromCart']
		)
			 ->whereNumber('book_id')
			 ->name('cart.remove');

		// My account
		Route::prefix('my-account')->group(function() {
			// Addresses
			Route::apiResource('addresses', AddressController::class)
				 ->except('show');
		});
	});
});
