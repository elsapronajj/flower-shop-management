<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\BouquetController;
use App\Http\Controllers\Api\BouquetFlowerController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\CustomerController;
use App\Http\Controllers\Api\DashboardController;
use App\Http\Controllers\Api\DeliveryController;
use App\Http\Controllers\Api\FlowerController;
use App\Http\Controllers\Api\OccasionController;
use App\Http\Controllers\Api\OrderController;
use App\Http\Controllers\Api\ReviewController;
use App\Http\Controllers\Api\RoleController;
use App\Http\Controllers\Api\SupplierController;
use App\Http\Controllers\Api\SupplyOrderController;
use App\Http\Controllers\Api\UserController;
use Illuminate\Support\Facades\Route;

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::post('/refresh-token', [AuthController::class, 'refresh']);

Route::middleware('jwt.auth')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/me', [AuthController::class, 'me']);
    Route::get('/dashboard', DashboardController::class);

    Route::apiResource('flowers', FlowerController::class);
    Route::apiResource('bouquets', BouquetController::class);
    Route::apiResource('bouquet-flowers', BouquetFlowerController::class)->parameters(['bouquet-flowers' => 'bouquetFlower']);
    Route::apiResource('categories', CategoryController::class);
    Route::apiResource('suppliers', SupplierController::class);
    Route::apiResource('customers', CustomerController::class);
    Route::apiResource('orders', OrderController::class);
    Route::apiResource('deliveries', DeliveryController::class);
    Route::apiResource('occasions', OccasionController::class);
    Route::apiResource('supply-orders', SupplyOrderController::class)->parameters(['supply-orders' => 'supplyOrder']);
    Route::apiResource('reviews', ReviewController::class);
    Route::apiResource('users', UserController::class);
    Route::apiResource('roles', RoleController::class);
});
