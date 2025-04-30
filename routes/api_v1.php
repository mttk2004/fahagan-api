<?php

use App\Http\Controllers\Api\V1\AuthorController;
use App\Http\Controllers\Api\V1\BookController;
use App\Http\Controllers\Api\V1\CustomerAddressController;
use App\Http\Controllers\Api\V1\CustomerCartItemController;
use App\Http\Controllers\Api\V1\CustomerOrderController;
use App\Http\Controllers\Api\V1\CustomerProfileController;
use App\Http\Controllers\Api\V1\DiscountController;
use App\Http\Controllers\Api\V1\GenreController;
use App\Http\Controllers\Api\V1\OrderController;
use App\Http\Controllers\Api\V1\PublisherController;
use App\Http\Controllers\Api\V1\SupplierController;
use App\Http\Controllers\Api\V1\UserController;
use Illuminate\Support\Facades\Route;

/**
 * UNAUTHENTICATED AREA
 */
Route::apiResources([
  'books' => BookController::class,
  'authors' => AuthorController::class,
  'publishers' => PublisherController::class,
  'genres' => GenreController::class,
], ['only' => ['index', 'show']]);

/**
 * AUTHENTICATED AREA
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
    // Customer profile
    Route::prefix('customer')->group(function () {
      // Cart
      Route::prefix('cart')->group(function () {
        Route::get('/', [CustomerCartItemController::class, 'index'])
          ->name('customer.cart.index');
        Route::post('/add', [CustomerCartItemController::class, 'addToCart'])
          ->name('customer.cart.add');
        Route::post('/update-quantity', [CustomerCartItemController::class, 'updateCartItemQuantity'])
          ->name('customer.cart.update-quantity');
        Route::delete(
          '/remove/{book_id}',
          [CustomerCartItemController::class, 'removeFromCart']
        )
          ->whereNumber('book_id')
          ->name('customer.cart.remove');
      });

      // Addresses
      Route::prefix('addresses')->group(function () {
        Route::get('/', [CustomerAddressController::class, 'index'])
          ->name('customer.addresses.index');
        Route::post('/', [CustomerAddressController::class, 'store'])
          ->name('customer.addresses.store');
        Route::patch('/{address}', [CustomerAddressController::class, 'update'])
          ->name('customer.addresses.update');
        Route::delete('/{address}', [CustomerAddressController::class, 'destroy'])
          ->name('customer.addresses.destroy');
      });

      // Profile
      Route::get('/profile', [CustomerProfileController::class, 'show'])
        ->name('customer.profile.show');
      Route::patch('/profile', [CustomerProfileController::class, 'update'])
        ->name('customer.profile.update');
      Route::delete('/profile', [CustomerProfileController::class, 'destroy'])
        ->name('customer.profile.destroy');

      // Orders
      Route::prefix('orders')->group(function () {
        Route::get('/', [CustomerOrderController::class, 'index'])
          ->name('customer.orders.index');
        Route::get('/{order}', [CustomerOrderController::class, 'show'])
          ->name('customer.orders.show');
        Route::post('/', [CustomerOrderController::class, 'store'])
          ->name('customer.orders.store');
        Route::post('/{order}/cancel', [CustomerOrderController::class, 'cancel'])
          ->name('customer.orders.cancel');
      });
    });
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
      'orders' => OrderController::class,
    ]);

    Route::prefix('genres')->group(function () {
      Route::post('restore/{genre}', [GenreController::class, 'restore'])
        ->name('genres.restore');
      Route::get('slug/{slug}', [GenreController::class, 'showBySlug'])
        ->name('genres.showBySlug');
    });

    Route::prefix('suppliers')->group(function () {
      Route::post('restore/{supplier}', [SupplierController::class, 'restore'])
        ->name('suppliers.restore');
    });
  });
});
