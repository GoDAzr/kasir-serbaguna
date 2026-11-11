<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\CustomerController;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\ReportController;
use App\Http\Controllers\Api\StockController;
use App\Http\Controllers\Api\TransactionController;
use App\Http\Middleware\EnsureRole;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Kasir Serbaguna (dipakai aplikasi Android)  -  prefix otomatis: /api
|--------------------------------------------------------------------------
*/

Route::get('/ping', fn () => response()->json(['status' => 'ok', 'app' => 'Kasir Serbaguna']));

Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:10,1');

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/me', [AuthController::class, 'me']);
    Route::post('/logout', [AuthController::class, 'logout']);

    // Dapat diakses admin & kasir
    Route::get('/categories', [CategoryController::class, 'index']);

    Route::get('/products', [ProductController::class, 'index']);
    Route::get('/products/barcode/{code}', [ProductController::class, 'byBarcode']);
    Route::get('/products/{product}', [ProductController::class, 'show']);

    Route::apiResource('customers', CustomerController::class);

    Route::get('/transactions', [TransactionController::class, 'index']);
    Route::post('/transactions', [TransactionController::class, 'store']);
    Route::get('/transactions/{transaction}', [TransactionController::class, 'show']);

    // Khusus admin
    Route::middleware(EnsureRole::class . ':admin')->group(function () {
        Route::post('/categories', [CategoryController::class, 'store']);
        Route::put('/categories/{category}', [CategoryController::class, 'update']);
        Route::delete('/categories/{category}', [CategoryController::class, 'destroy']);

        Route::post('/products', [ProductController::class, 'store']);
        Route::put('/products/{product}', [ProductController::class, 'update']);
        Route::delete('/products/{product}', [ProductController::class, 'destroy']);

        Route::get('/products/{product}/stock', [StockController::class, 'history']);
        Route::post('/products/{product}/stock', [StockController::class, 'update']);

        Route::post('/transactions/{transaction}/void', [TransactionController::class, 'void']);

        Route::get('/reports/summary', [ReportController::class, 'summary']);
    });
});
