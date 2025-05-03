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
 * PUBLIC ROUTES - NO AUTHENTICATION REQUIRED
 */
// Public resource routes with only index and show actions
Route::apiResources([
  'books' => BookController::class,
  'authors' => AuthorController::class,
  'publishers' => PublisherController::class,
  'genres' => GenreController::class,
], ['only' => ['index', 'show']]);

/**
 * AUTHENTICATED ROUTES
 */
Route::middleware('auth.*')->group(function () {
  // Common authenticated routes for both customers and employees
  Route::apiResource('users', UserController::class)->except('store');

  /**
   * CUSTOMER ROUTES
   */
  Route::middleware('auth.customer')->prefix('customer')->group(function () {
    // Cart routes
    Route::prefix('cart')->controller(CustomerCartItemController::class)->group(function () {
      Route::get('/', 'index')->name('customer.cart.index');
      Route::post('/add', 'addToCart')->name('customer.cart.add');
      Route::post('/update-quantity', 'updateCartItemQuantity')->name('customer.cart.update-quantity');
      Route::delete('/remove/{book_id}', 'removeFromCart')->whereNumber('book_id')->name('customer.cart.remove');
    });

    // Address routes
    Route::prefix('addresses')->controller(CustomerAddressController::class)->group(function () {
      Route::get('/', 'index')->name('customer.addresses.index');
      Route::post('/', 'store')->name('customer.addresses.store');
      Route::patch('/{address}', 'update')->name('customer.addresses.update');
      Route::delete('/{address}', 'destroy')->name('customer.addresses.destroy');
    });

    // Profile routes
    Route::controller(CustomerProfileController::class)->group(function () {
      Route::get('/profile', 'show')->name('customer.profile.show');
      Route::patch('/profile', 'update')->name('customer.profile.update');
      Route::delete('/profile', 'destroy')->name('customer.profile.destroy');
    });

    // Customer orders routes
    Route::prefix('orders')->controller(CustomerOrderController::class)->group(function () {
      Route::get('/', 'index')->name('customer.orders.index');
      Route::get('/{order}', 'show')->name('customer.orders.show');
      Route::post('/', 'store')->name('customer.orders.store');
      Route::post('/{order}/cancel', 'cancel')->name('customer.orders.cancel');
    });
  });

  /**
   * EMPLOYEE ROUTES
   */
  Route::middleware('auth.employee')->group(function () {
    // CRUD resources except index and show (which are public)
    Route::apiResources([
      'books' => BookController::class,
      'authors' => AuthorController::class,
      'publishers' => PublisherController::class,
      'genres' => GenreController::class,
    ], ['except' => ['index', 'show']]);

    // Full CRUD resources (employee only)
    Route::apiResources([
      'discounts' => DiscountController::class,
      'suppliers' => SupplierController::class,
    ]);

    // Additional genre routes
    Route::prefix('genres')->controller(GenreController::class)->group(function () {
      Route::post('restore/{genre}', 'restore')->name('genres.restore');
      Route::get('slug/{slug}', 'showBySlug')->name('genres.showBySlug');
    });

    // Additional supplier routes
    Route::post('suppliers/restore/{supplier}', [SupplierController::class, 'restore'])
      ->name('suppliers.restore');

    // Full routes for orders
    Route::prefix('orders')->controller(OrderController::class)->group(function () {
      Route::get('/', 'index')->name('orders.index');
      Route::get('/{order_id}', 'show')->name('orders.show');
      Route::patch('/{order_id}/status', [OrderController::class, 'updateStatus']);
    });
  });
});
