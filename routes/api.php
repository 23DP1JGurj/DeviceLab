<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\OrderController;
use App\Models\Branch;
use App\Models\Device;
use App\Models\Part;
use App\Models\Service;
use App\Models\User;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Http\Request;
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
        Route::post('/login', [AuthController::class, 'login']);
        Route::post('/logout', [AuthController::class, 'logout']);
        Route::get('/me', [AuthController::class, 'me']);
    });

Route::get('/branches', fn () => Branch::query()
    ->select('id', 'name', 'address')
    ->where('is_active', 1)
    ->orderBy('name')
    ->get());

Route::get('/services', fn () => Service::query()
    ->select('id', 'name', 'base_price')
    ->where('is_active', 1)
    ->orderBy('name')
    ->get());

Route::get('/parts', fn () => Part::query()
    ->select('id', 'name', 'unit_price', 'stock_qty')
    ->where('is_active', 1)
    ->orderBy('name')
    ->get());

Route::middleware(array_merge($sessionMiddleware, ['auth']))->group(function () {
    Route::get('/my/devices', function (Request $request) {
        return Device::query()
            ->select('id', 'user_id', 'brand', 'model', 'type')
            ->where('user_id', $request->user()->id)
            ->orderByDesc('id')
            ->get();
    });

    Route::get('/devices', function (Request $request) {
        $query = Device::query()
            ->select('id', 'user_id', 'brand', 'model', 'type')
            ->orderByDesc('id');

        if ($request->user()->hasRole(User::ROLE_CLIENT)) {
            $query->where('user_id', $request->user()->id);
        }

        return $query->get();
    });

    Route::post('/orders', [OrderController::class, 'store']);
    Route::get('/orders/{order}', [OrderController::class, 'show']);
});

Route::prefix('client')
    ->middleware(array_merge($sessionMiddleware, ['auth', 'role:client,admin']))
    ->group(function () {
        Route::get('/orders', [OrderController::class, 'clientIndex']);
    });

Route::prefix('staff')
    ->middleware(array_merge($sessionMiddleware, ['auth', 'role:staff,admin']))
    ->group(function () {
        Route::get('/orders', [OrderController::class, 'index']);
        Route::get('/orders/{order}', [OrderController::class, 'show']);
        Route::patch('/orders/{order}', [OrderController::class, 'update']);
        Route::delete('/orders/{order}', [OrderController::class, 'destroy']);
    });
