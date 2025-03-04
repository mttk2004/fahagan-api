<?php

use App\Http\Controllers\Api\V1\UserController;


Route::get('/users', [UserController::class, 'index']);
