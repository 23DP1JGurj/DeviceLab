<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\AdminController;
use App\Http\Controllers\Api\DeviceCatalogController;
use App\Http\Controllers\Api\MyDeviceController;
use App\Http\Controllers\Api\NotificationController;
use App\Http\Controllers\Api\OrderAttachmentController;
use App\Http\Controllers\Api\OrderController;
use App\Http\Controllers\Api\ReviewController;
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
    ->select('id', 'name', 'description', 'base_price', 'is_active')
    ->where('is_active', 1)
    ->orderBy('name')
    ->get());

Route::get('/parts', fn () => Part::query()
    ->select('id', 'name', 'sku', 'unit_price', 'stock_qty', 'is_active')
    ->where('is_active', 1)
    ->orderBy('name')
    ->get());

Route::get('/device-brands', [DeviceCatalogController::class, 'brands']);
Route::get('/device-models', [DeviceCatalogController::class, 'models']);
Route::get('/device-model-suggestions', [DeviceCatalogController::class, 'suggestions']);
Route::get('/reviews/public', [ReviewController::class, 'publicIndex']);

Route::patch('/me/password', [AuthController::class, 'updatePassword'])
    ->middleware(array_merge($sessionMiddleware, ['auth']));

Route::prefix('notifications')
    ->middleware(array_merge($sessionMiddleware, ['auth']))
    ->group(function () {
        Route::get('/', [NotificationController::class, 'index']);
        Route::get('/unread-count', [NotificationController::class, 'unreadCount']);
        Route::patch('/{notification}/read', [NotificationController::class, 'markRead']);
        Route::patch('/mark-all-read', [NotificationController::class, 'markAllRead']);
        Route::post('/read-all', [NotificationController::class, 'markAllRead']);
    });

Route::prefix('my')
    ->middleware(array_merge($sessionMiddleware, ['auth', 'role:client']))
    ->group(function () {
        Route::get('/devices', [MyDeviceController::class, 'index']);
        Route::post('/devices', [MyDeviceController::class, 'store']);
        Route::delete('/devices/{device}', [MyDeviceController::class, 'destroy']);
        Route::get('/orders', [OrderController::class, 'clientIndex']);
        Route::post('/orders', [OrderController::class, 'store']);
        Route::get('/orders/history', [OrderController::class, 'clientIndex']);
        Route::patch('/orders/{order}/cancel', [OrderController::class, 'cancel']);
        Route::get('/orders/{order}/attachments', [OrderAttachmentController::class, 'clientIndex']);
        Route::post('/orders/{order}/attachments', [OrderAttachmentController::class, 'store']);
        Route::delete('/orders/{order}/attachments/{attachment}', [OrderAttachmentController::class, 'destroy']);
        Route::get('/orders/{order}/payment', [OrderController::class, 'payment']);
        Route::post('/orders/{order}/pay', [OrderController::class, 'pay']);
        Route::post('/orders/{order}/review', [ReviewController::class, 'store']);
        Route::get('/orders/{order}', [OrderController::class, 'show']);
        Route::get('/reviews', [ReviewController::class, 'mine']);
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
        Route::get('/reviews', [ReviewController::class, 'staffIndex']);
        Route::get('/orders/unassigned', [OrderController::class, 'unassigned']);
        Route::get('/orders/my', [OrderController::class, 'assignedToMe']);
        Route::get('/orders/history', [OrderController::class, 'staffHistory']);
        Route::get('/orders', [OrderController::class, 'index']);
        Route::get('/orders/{order}/attachments', [OrderAttachmentController::class, 'staffIndex']);
        Route::get('/orders/{order}', [OrderController::class, 'show']);
        Route::post('/orders/{order}/claim', [OrderController::class, 'claim']);
        Route::patch('/orders/{order}', [OrderController::class, 'update']);
        Route::delete('/orders/{order}', [OrderController::class, 'destroy']);
        Route::post('/orders/{order}/items', [OrderController::class, 'storeItem']);
        Route::patch('/orders/{order}/items/{item}', [OrderController::class, 'updateItem']);
        Route::delete('/orders/{order}/items/{item}', [OrderController::class, 'destroyItem']);
    });

Route::prefix('admin')
    ->middleware(array_merge($sessionMiddleware, ['auth', 'role:admin']))
    ->group(function () {
        Route::get('/summary', [AdminController::class, 'summary']);
        Route::get('/orders', [AdminController::class, 'orders']);
        Route::get('/orders/{order}', [AdminController::class, 'showOrder']);
        Route::get('/clients', [AdminController::class, 'clients']);
        Route::get('/staff', [AdminController::class, 'staff']);
        Route::get('/reviews', [AdminController::class, 'reviews']);
        Route::patch('/users/{user}/block', [AdminController::class, 'blockUser']);
        Route::patch('/users/{user}/unblock', [AdminController::class, 'unblockUser']);
    });
