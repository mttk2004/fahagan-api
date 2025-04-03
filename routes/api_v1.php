<?php

use App\Http\Controllers\Api\V1\AddressController;
use App\Http\Controllers\Api\V1\AuthorController;
use App\Http\Controllers\Api\V1\BookController;
use App\Http\Controllers\Api\V1\CartItemController;
use App\Http\Controllers\Api\V1\DiscountController;
use App\Http\Controllers\Api\V1\GenreController;
use App\Http\Controllers\Api\V1\PublisherController;
use App\Http\Controllers\Api\V1\SupplierController;
use App\Http\Controllers\Api\V1\UserController;
use Illuminate\Support\Facades\Route;

/**
 * Unauthenticated area
 */
Route::apiResources([
    'books' => BookController::class,
    'authors' => AuthorController::class,
    'publishers' => PublisherController::class,
    'genres' => GenreController::class,
], ['only' => ['index', 'show']]);

/**
 * Authenticated area
 */
Route::middleware('auth.*')->group(function () {
    /**
     * Both customer and employee can access
     */
    Route::apiResource('users', UserController::class)
         ->except('store');

    /**
     * Customer only area
     */
    Route::middleware('auth.customer')->group(function () {
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

        // Addresses
        Route::get('addresses', [AddressController::class, 'index'])
             ->name('addresses.index');
        Route::post('addresses', [AddressController::class, 'store'])
             ->name('addresses.store');
        Route::patch('addresses/{address_id}', [AddressController::class, 'update'])
             ->name('addresses.update');
        Route::delete('addresses/{address_id}', [AddressController::class, 'destroy'])
             ->name('addresses.destroy');
    });

    /**
     * Employee only area
     */
    Route::middleware('auth.employee')->group(function () {
        Route::apiResources([
            'books' => BookController::class,
            'authors' => AuthorController::class,
            'publishers' => PublisherController::class,
            'genres' => GenreController::class,
        ], ['except' => ['index', 'show']]);

        Route::apiResources([
            'discounts' => DiscountController::class,
            'suppliers' => SupplierController::class,
        ]);
    });
});

// Genres routes
Route::middleware('auth.*')
    ->prefix('genres')
    ->group(function () {
        Route::post('/restore/{genre}', [GenreController::class, 'restore'])
            ->name('genres.restore');
    });

Route::apiResource('genres', GenreController::class);
Route::get('genres/slug/{slug}', [GenreController::class, 'showBySlug'])
    ->name('genres.showBySlug');

// Suppliers routes
Route::middleware('auth.*')
    ->prefix('suppliers')
    ->group(function () {
        Route::post('/restore/{supplier}', [SupplierController::class, 'restore'])
            ->name('suppliers.restore');
    });
