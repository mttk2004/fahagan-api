<?php

use App\Http\Controllers\Api\V1\AdminCustomerController;
use App\Http\Controllers\Api\V1\AdminEmployeeController;
use App\Http\Controllers\Api\V1\AdminRoleController;
use App\Http\Controllers\Api\V1\AdminStatsController;
use App\Http\Controllers\Api\V1\AuthorController;
use App\Http\Controllers\Api\V1\BookController;
use App\Http\Controllers\Api\V1\CustomerAddressController;
use App\Http\Controllers\Api\V1\CustomerCartItemController;
use App\Http\Controllers\Api\V1\CustomerOrderController;
use App\Http\Controllers\Api\V1\CustomerProfileController;
use App\Http\Controllers\Api\V1\DiscountController;
use App\Http\Controllers\Api\V1\GenreController;
use App\Http\Controllers\Api\V1\OrderController;
use App\Http\Controllers\Api\V1\PaymentController;
use App\Http\Controllers\Api\V1\PublisherController;
use App\Http\Controllers\Api\V1\SearchBookController;
use App\Http\Controllers\Api\V1\StockImportController;
use App\Http\Controllers\Api\V1\SupplierController;
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

// Search routes
Route::prefix('search')->group(function () {
    Route::get('/books', [SearchBookController::class, 'search'])->name('search.books');
});

// Additional genre route
Route::get('genres/slug/{slug}', [GenreController::class, 'showBySlug'])->name('genres.showBySlug');

// VNPay callback route
Route::get('/payments/vnpay-return', [PaymentController::class, 'handleVNPayReturn'])
  ->name('vnpay.return');

/**
 * AUTHENTICATED ROUTES
 */
Route::middleware('auth.*')->group(function () {
    /**
     * CUSTOMER ROUTES
     */
    Route::middleware('auth.customer')->prefix('customer')->group(function () {
        // Cart routes
        Route::prefix('cart')->controller(CustomerCartItemController::class)->group(function () {
            Route::get('/', 'index')->name('customer.cart.index');
            Route::post('/add', 'addToCart')->name('customer.cart.add');
            Route::post('/update-quantity', 'updateCartItemQuantity')
              ->name('customer.cart.update-quantity');
            Route::delete('/remove/{book_id}', 'removeFromCart')
              ->whereNumber('book_id')
              ->name('customer.cart.remove');
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
            Route::post('/{order}/complete', 'complete')->name('customer.orders.complete');
        });

        // Payment routes
        Route::get('/orders/{order}/pay-vnpay', [PaymentController::class, 'createVNPayPayment'])
          ->name('customer.order.pay-vnpay');
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

        // Full routes for stock imports
        Route::apiResource('stock-imports', StockImportController::class)->only('index', 'store');
    });

    /**
     * ADMIN ROUTES
     */
    Route::middleware('auth.admin')->prefix('admin')->group(function () {
        // Employee management routes
        Route::prefix('employees')->controller(AdminEmployeeController::class)->group(function () {
            Route::get('/', 'index')->name('admin.employees.index');
            Route::get('/trashed', 'trashed')->name('admin.employees.trashed');
            Route::get('/{employee}', 'show')->name('admin.employees.show');
            Route::post('/', 'store')->name('admin.employees.store');
            Route::delete('/{employee}', 'destroy')->name('admin.employees.destroy');
            Route::post('/{employee}/restore', 'restore')->name('admin.employees.restore');

            Route::post('/{employee}/permissions/add', 'addPermissions')
              ->name('admin.employees.permissions.add');
            Route::post('/{employee}/permissions/remove', 'removePermissions')
              ->name('admin.employees.permissions.remove');
            Route::post('/{employee}/permissions/sync', 'syncPermissions')
              ->name('admin.employees.permissions.sync');

            Route::post('/{employee}/roles/add', 'addRole')->name('admin.employees.add-role');
            Route::post('/{employee}/roles/remove', 'removeRole')
              ->name('admin.employees.remove-role');
            Route::post('/{employee}/roles/sync', 'syncRoles')->name('admin.employees.sync-roles');
        });

        // Customer management routes
        Route::prefix('customers')->controller(AdminCustomerController::class)->group(function () {
            Route::get('/', 'index')->name('admin.customers.index');
            Route::get('/trashed', 'trashed')->name('admin.customers.trashed');
            Route::get('/{customer}', 'show')->name('admin.customers.show');
            Route::delete('/{customer}', 'destroy')->name('admin.customers.destroy');
            Route::get('/{customer}/orders', 'showOrders')->name('admin.customers.show-orders');
            Route::post('/{customer}/restore', 'restore')->name('admin.customers.restore');
        });

        // Role management routes
        Route::prefix('roles')->controller(AdminRoleController::class)->group(function () {
            Route::get('/', 'index')->name('admin.roles.index');
            Route::post('/{role}/permissions/add', 'addPermissions')
              ->name('admin.roles.permissions.add');
            Route::post('/{role}/permissions/remove', 'removePermissions')
              ->name('admin.roles.permissions.remove');
            Route::post('/{role}/permissions/sync', 'syncPermissions')
              ->name('admin.roles.permissions.sync');
        });

        // Stats routes
        Route::prefix('stats')->controller(AdminStatsController::class)->group(function () {
            Route::get('/', 'index')->name('admin.stats.index');
        });
    });
});
