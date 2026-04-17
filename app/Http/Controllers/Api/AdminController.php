<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Payment;
use App\Models\Review;
use App\Models\User;

class AdminController extends Controller
{
    public function summary()
    {
        return response()->json([
            'total_orders' => Order::query()->count(),
            'active_orders' => Order::query()->whereNotIn('status', ['done', 'cancelled'])->count(),
            'completed_orders' => Order::query()->where('status', 'done')->count(),
            'paid_orders' => Payment::query()->where('status', Payment::STATUS_PAID)->count(),
            'total_revenue' => (float) Payment::query()->where('status', Payment::STATUS_PAID)->sum('amount'),
            'average_rating' => round((float) Review::query()->avg('rating'), 2),
            'total_clients' => User::query()->where('role', User::ROLE_CLIENT)->count(),
            'total_staff' => User::query()->where('role', User::ROLE_STAFF)->count(),
        ]);
    }

    public function orders()
    {
        return Order::query()
            ->with([
                'user:id,name,email,phone',
                'branch:id,name,address',
                'device:id,type,component_type,brand,model,specs,serial_number',
                'assignedStaff:id,name,email,phone,specialization',
                'payment:id,order_id,user_id,amount,status,paid_at,method',
                'review:id,order_id,user_id,branch_id,staff_id,rating,comment,created_at',
                'items.service:id,name,base_price',
                'items.part:id,name,unit_price',
                'attachments:id,order_id,user_id,file_path,original_name,mime_type,file_size,created_at',
            ])
            ->latest()
            ->paginate(20);
    }

    public function showOrder(Order $order)
    {
        return $order->load([
            'user:id,name,email,phone',
            'branch:id,name,address',
            'device:id,type,component_type,brand,model,specs,serial_number',
            'assignedStaff:id,name,email,phone,specialization',
            'payment:id,order_id,user_id,amount,status,paid_at,method',
            'review.user:id,name,email',
            'review.branch:id,name,address',
            'review.staff:id,name,email,specialization',
            'items.service:id,name,base_price',
            'items.part:id,name,unit_price',
            'statusHistory.changedBy:id,name,email',
            'attachments:id,order_id,user_id,file_path,original_name,mime_type,file_size,created_at',
        ]);
    }

    public function clients()
    {
        return User::query()
            ->select('id', 'name', 'email', 'phone', 'created_at')
            ->where('role', User::ROLE_CLIENT)
            ->withCount(['orders', 'devices'])
            ->withMax(['orders as latest_order_date'], 'created_at')
            ->orderBy('name')
            ->paginate(20);
    }

    public function staff()
    {
        return User::query()
            ->select('id', 'name', 'email', 'phone', 'specialization', 'branch_id')
            ->where('role', User::ROLE_STAFF)
            ->with('branch:id,name,address')
            ->withCount([
                'assignedOrders as assigned_orders_count',
                'assignedOrders as completed_orders_count' => fn ($query) => $query->where('status', 'done'),
            ])
            ->withAvg(['staffReviews as average_rating'], 'rating')
            ->orderBy('name')
            ->paginate(20);
    }

    public function reviews()
    {
        return Review::query()
            ->with([
                'user:id,name,email',
                'staff:id,name,email,specialization',
                'branch:id,name,address',
                'order:id,order_number,status,final_cost',
            ])
            ->latest()
            ->paginate(20);
    }
}
