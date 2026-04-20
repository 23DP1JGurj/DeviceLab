<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Payment;
use App\Models\Review;
use App\Models\User;
use Illuminate\Http\Request;

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

    public function orders(Request $request)
    {
        $query = Order::query()
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
            ]);

        if ($request->filled('search')) {
            $search = trim((string) $request->input('search'));
            $query->where(function ($query) use ($search) {
                $query->where('order_number', 'like', "%{$search}%")
                    ->orWhereHas('user', fn ($userQuery) => $userQuery->where('name', 'like', "%{$search}%"))
                    ->orWhereHas('assignedStaff', fn ($staffQuery) => $staffQuery->where('name', 'like', "%{$search}%"))
                    ->orWhereHas('device', fn ($deviceQuery) => $deviceQuery->where('brand', 'like', "%{$search}%")->orWhere('model', 'like', "%{$search}%"));
            });
        }

        if ($request->filled('status')) {
            $query->where('status', (string) $request->input('status'));
        }

        if ($request->filled('branch_id')) {
            $query->where('branch_id', (int) $request->input('branch_id'));
        }

        if ($request->filled('assigned_staff_id')) {
            $query->where('assigned_staff_id', (int) $request->input('assigned_staff_id'));
        }

        if ($request->filled('payment_status')) {
            if ($request->input('payment_status') === 'paid') {
                $query->whereHas('payment', fn ($paymentQuery) => $paymentQuery->where('status', Payment::STATUS_PAID));
            } else {
                $query->where(function ($query) {
                    $query->whereDoesntHave('payment')
                        ->orWhereHas('payment', fn ($paymentQuery) => $paymentQuery->where('status', '!=', Payment::STATUS_PAID));
                });
            }
        }

        if ($request->filled('has_review')) {
            $request->boolean('has_review') ? $query->whereHas('review') : $query->whereDoesntHave('review');
        }

        $request->input('sort') === 'oldest'
            ? $query->oldest('created_at')
            : $query->latest('created_at');

        return $query->paginate(20);
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
            ->addSelect('is_blocked')
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
            ->addSelect('is_blocked')
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

    public function blockUser(Request $request, User $user)
    {
        if ($user->id === $request->user()->id) {
            abort(422, 'Administratoru nevar bloķēt pašam sevi.');
        }

        $user->forceFill(['is_blocked' => true])->save();

        return response()->json(['user' => $user->fresh()]);
    }

    public function unblockUser(User $user)
    {
        $user->forceFill(['is_blocked' => false])->save();

        return response()->json(['user' => $user->fresh()]);
    }
}
