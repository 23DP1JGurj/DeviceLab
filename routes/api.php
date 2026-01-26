<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\OrderController;

Route::apiResource('orders', OrderController::class);

use App\Models\Branch;
use App\Models\Device;
use App\Models\Service;
use App\Models\Part;

Route::get('/branches', fn () =>
    Branch::select('id','name')->where('is_active',1)->orderBy('name')->get()
);

Route::get('/devices', fn () =>
    Device::select('id','brand','model')->orderBy('id','desc')->get()
);

Route::get('/services', fn () =>
    Service::select('id','name','base_price')->where('is_active',1)->orderBy('name')->get()
);

Route::get('/parts', fn () =>
    Part::select('id','name','unit_price','stock_qty')->where('is_active',1)->orderBy('name')->get()
);
