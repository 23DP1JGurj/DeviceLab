<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\UserNotification;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function index(Request $request)
    {
        $limit = min(max((int) $request->input('limit', 50), 1), 50);
        $query = UserNotification::query()
            ->with('order:id,order_number,status')
            ->where('user_id', $request->user()->id);

        if ($request->input('scope', 'all') === 'unread') {
            $query->whereNull('read_at');
        }

        if ($request->user()->role === 'client') {
            $query->whereIn('type', $this->clientImportantTypes());
        }

        if ($request->input('group') === 'order') {
            $notifications = $query
                ->latest()
                ->limit($limit)
                ->get();

            $groups = $notifications
                ->groupBy(fn (UserNotification $notification) => $notification->order_id ?: 'general')
                ->map(function ($items) {
                    $first = $items->first();

                    return [
                        'order_id' => $first->order_id,
                        'order_number' => $first->order?->order_number ?? 'Paziņojumi',
                        'order_status' => $first->order?->status,
                        'unread_count' => $items->whereNull('read_at')->count(),
                        'last_items' => $items->take(3)->values(),
                    ];
                })
                ->values();

            return response()->json([
                'data' => $groups,
                'unread_count' => $this->unreadQuery($request)->count(),
            ]);
        }

        return $query
            ->latest()
            ->limit($limit)
            ->get();
    }

    public function unreadCount(Request $request)
    {
        return response()->json([
            'unread_count' => $this->unreadQuery($request)->count(),
        ]);
    }

    public function markRead(Request $request, UserNotification $notification)
    {
        if ($notification->user_id !== $request->user()->id) {
            abort(403);
        }

        if ($notification->read_at === null) {
            $notification->forceFill(['read_at' => now()])->save();
        }

        return $notification->fresh()->load('order:id,order_number,status');
    }

    public function markAllRead(Request $request)
    {
        UserNotification::query()
            ->where('user_id', $request->user()->id)
            ->whereNull('read_at')
            ->update(['read_at' => now()]);

        return response()->json(['ok' => true]);
    }

    private function unreadQuery(Request $request)
    {
        $query = UserNotification::query()
            ->where('user_id', $request->user()->id)
            ->whereNull('read_at');

        if ($request->user()->role === 'client') {
            $query->whereIn('type', $this->clientImportantTypes());
        }

        return $query;
    }

    private function clientImportantTypes(): array
    {
        return [
            'order_claimed',
            'order_status_changed',
            'order_ready',
            'order_paid',
        ];
    }
}
