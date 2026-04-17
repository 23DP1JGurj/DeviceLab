<?php

namespace App\Services;

use App\Models\Order;
use App\Models\User;
use App\Models\UserNotification;

class NotificationService
{
    public function notify(User|int $user, string $type, string $title, string $message, ?Order $order = null, array $data = []): UserNotification
    {
        $userId = $user instanceof User ? $user->id : $user;

        return UserNotification::create([
            'user_id' => $userId,
            'order_id' => $order?->id,
            'type' => $type,
            'title' => $title,
            'message' => $message,
            'data' => $data === [] ? null : $data,
        ]);
    }

    public function notifyMany(iterable $users, string $type, string $title, string $message, ?Order $order = null, array $data = []): void
    {
        $seen = [];

        foreach ($users as $user) {
            $userId = $user instanceof User ? $user->id : (int) $user;

            if ($userId <= 0 || isset($seen[$userId])) {
                continue;
            }

            $seen[$userId] = true;
            $this->notify($userId, $type, $title, $message, $order, $data);
        }
    }
}
