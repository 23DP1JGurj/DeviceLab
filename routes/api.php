<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\OrderController;
use App\Models\Branch;
use App\Models\Device;
use App\Models\Part;
use App\Models\Service;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::prefix('auth')->group(function () {
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login', [AuthController::class, 'login']);

    Route::middleware('auth:sanctum')->group(function () {
        Route::post('/logout', [AuthController::class, 'logout']);
        Route::get('/me', [AuthController::class, 'me']);
    });
});

Route::get('/branches', fn () => Branch::query()
    ->select('id', 'name')
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

Route::middleware(['auth:sanctum', 'role:client,staff,admin'])->group(function () {
    Route::get('/devices', function (Request $request) {
        $query = Device::query()
            ->select('id', 'user_id', 'brand', 'model', 'type')
            ->orderByDesc('id');

        if ($request->user()->hasRole(User::ROLE_CLIENT)) {
            $query->where('user_id', $request->user()->id);
        }

        return $query->get();
    });

    Route::get('/my/orders', [OrderController::class, 'myOrders']);
    Route::get('/orders', [OrderController::class, 'clientIndex']);
    Route::post('/orders', [OrderController::class, 'store']);
    Route::get('/orders/{order}', [OrderController::class, 'show']);
});

Route::prefix('staff')
    ->middleware(['auth:sanctum', 'role:staff,admin'])
    ->group(function () {
        Route::get('/orders', [OrderController::class, 'index']);
        Route::get('/orders/{order}', [OrderController::class, 'show']);
        Route::patch('/orders/{order}', [OrderController::class, 'update']);
        Route::delete('/orders/{order}', [OrderController::class, 'destroy']);
    });
