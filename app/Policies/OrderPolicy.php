<?php

namespace App\Policies;

use App\Models\Order;
use App\Models\User;

class OrderPolicy
{
    public function view(User $user, Order $order): bool
    {
        if ($user->hasRole(User::ROLE_ADMIN)) {
            return true;
        }

        if ($user->hasRole(User::ROLE_STAFF)) {
            return $order->assigned_staff_id === null || $order->assigned_staff_id === $user->id;
        }

        return $order->user_id === $user->id;
    }

    public function update(User $user, Order $order): bool
    {
        if ($user->hasRole(User::ROLE_ADMIN)) {
            return true;
        }

        return $user->hasRole(User::ROLE_STAFF) && $order->assigned_staff_id === $user->id;
    }

    public function delete(User $user, Order $order): bool
    {
        if ($user->hasRole(User::ROLE_ADMIN)) {
            return true;
        }

        return $user->hasRole(User::ROLE_STAFF) && $order->assigned_staff_id === $user->id;
    }
}
