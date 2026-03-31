<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\DeviceCatalogController;
use App\Http\Controllers\Api\MyDeviceController;
use App\Http\Controllers\Api\OrderController;
use App\Models\Branch;
use App\Models\Part;
use App\Models\Service;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\Support\Facades\Route;

$sessionMiddleware = [
    EncryptCookies::class,
    AddQueuedCookiesToResponse::class,
    StartSession::class,
];

Route::prefix('auth')
    ->middleware($sessionMiddleware)
    ->group(function () {
        Route::post('/register', [AuthController::class, 'register']);
        Route::post('/login', [AuthController::class, 'login']);
        Route::middleware('auth')->group(function () {
            Route::post('/logout', [AuthController::class, 'logout']);
            Route::get('/me', [AuthController::class, 'me']);
            Route::patch('/profile', [AuthController::class, 'updateProfile']);
        });
    });

Route::get('/branches', fn () => Branch::query()
    ->select('id', 'name', 'address', 'phone', 'email', 'working_hours')
    ->where('is_active', 1)
    ->orderBy('name')
    ->get());

Route::get('/services', fn () => Service::query()
    ->select('id', 'name', 'description', 'base_price')
    ->where('is_active', 1)
    ->orderBy('name')
    ->get());

Route::get('/parts', fn () => Part::query()
    ->select('id', 'name', 'sku', 'unit_price', 'stock_qty')
    ->where('is_active', 1)
    ->orderBy('name')
    ->get());

Route::get('/device-brands', [DeviceCatalogController::class, 'brands']);
Route::get('/device-models', [DeviceCatalogController::class, 'models']);

Route::prefix('my')
    ->middleware(array_merge($sessionMiddleware, ['auth', 'role:client']))
    ->group(function () {
        Route::get('/devices', [MyDeviceController::class, 'index']);
        Route::post('/devices', [MyDeviceController::class, 'store']);
        Route::delete('/devices/{device}', [MyDeviceController::class, 'destroy']);
        Route::get('/orders', [OrderController::class, 'clientIndex']);
        Route::post('/orders', [OrderController::class, 'store']);
        Route::get('/orders/{order}', [OrderController::class, 'show']);
    });

Route::prefix('client')
    ->middleware(array_merge($sessionMiddleware, ['auth', 'role:client']))
    ->group(function () {
        Route::get('/orders', [OrderController::class, 'clientIndex']);
        Route::post('/orders', [OrderController::class, 'store']);
        Route::get('/orders/{order}', [OrderController::class, 'show']);
    });

Route::prefix('staff')
    ->middleware(array_merge($sessionMiddleware, ['auth', 'role:staff,admin']))
    ->group(function () {
        Route::get('/orders', [OrderController::class, 'index']);
        Route::get('/orders/{order}', [OrderController::class, 'show']);
        Route::patch('/orders/{order}', [OrderController::class, 'update']);
        Route::delete('/orders/{order}', [OrderController::class, 'destroy']);
    });
