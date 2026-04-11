<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Payment;
use App\Models\Review;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ReviewController extends Controller
{
    public function store(Request $request, Order $order)
    {
        $user = $request->user();

        if ($order->user_id !== $user->id) {
            abort(403);
        }

        $data = $request->validate([
            'rating' => ['required', 'integer', 'between:1,5'],
            'comment' => ['nullable', 'string', 'max:2000'],
        ]);

        if ($order->status !== 'done') {
            throw ValidationException::withMessages([
                'order' => ['Atsauksmi var atstāt tikai pēc pabeigta pasūtījuma.'],
            ]);
        }

        if ($order->payment?->status !== Payment::STATUS_PAID) {
            throw ValidationException::withMessages([
                'payment' => ['Atsauksmi var atstāt tikai pēc apmaksāta pasūtījuma.'],
            ]);
        }

        return DB::transaction(function () use ($data, $order, $user) {
            $lockedOrder = Order::query()
                ->whereKey($order->id)
                ->lockForUpdate()
                ->firstOrFail();

            if ($lockedOrder->review()->exists()) {
                abort(409, 'Atsauksme šim pasūtījumam jau ir iesniegta.');
            }

            $review = Review::create([
                'order_id' => $lockedOrder->id,
                'user_id' => $user->id,
                'branch_id' => $lockedOrder->branch_id,
                'staff_id' => $lockedOrder->assigned_staff_id,
                'rating' => (int) $data['rating'],
                'comment' => $data['comment'] ?? null,
            ]);

            return response()->json(
                $review->load([
                    'order:id,order_number,status,final_cost',
                    'user:id,name,email',
                    'branch:id,name,address',
                    'staff:id,name,email,specialization',
                ]),
                201
            );
        });
    }

    public function mine(Request $request)
    {
        return Review::query()
            ->with([
                'order:id,order_number,status,final_cost',
                'branch:id,name,address',
                'staff:id,name,email,specialization',
            ])
            ->where('user_id', $request->user()->id)
            ->latest()
            ->paginate(10);
    }

    public function staffIndex(Request $request)
    {
        $user = $request->user();

        $query = Review::query()
            ->with([
                'user:id,name,email',
                'order:id,order_number,status,final_cost',
                'branch:id,name,address',
                'staff:id,name,email,specialization',
            ]);

        if ($user->hasRole(User::ROLE_STAFF)) {
            $query->where('staff_id', $user->id);
        }

        if ($request->filled('rating')) {
            $query->where('rating', (int) $request->input('rating'));
        }

        if ($request->filled('branch_id')) {
            $query->where('branch_id', (int) $request->input('branch_id'));
        }

        if ($user->hasRole(User::ROLE_ADMIN) && $request->filled('staff_id')) {
            $query->where('staff_id', (int) $request->input('staff_id'));
        }

        if ($request->filled('search')) {
            $search = (string) $request->input('search');
            $query->where(function ($query) use ($search) {
                $query->whereHas('user', fn ($userQuery) => $userQuery->where('name', 'like', "%{$search}%"))
                    ->orWhereHas('order', fn ($orderQuery) => $orderQuery->where('order_number', 'like', "%{$search}%"));
            });
        }

        $averageRating = round((clone $query)->avg('rating') ?? 0, 2);
        $paginated = $query->latest()->paginate(10);

        return response()->json([
            'data' => $paginated->items(),
            'current_page' => $paginated->currentPage(),
            'last_page' => $paginated->lastPage(),
            'per_page' => $paginated->perPage(),
            'total' => $paginated->total(),
            'average_rating' => $averageRating,
        ]);
    }
}
