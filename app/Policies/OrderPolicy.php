<?php

namespace App\Policies;

use App\Models\Order;
use App\Models\User;

class OrderPolicy
{
    /**
     * Admins can view all orders.
     * Customers can only view their own orders.
     */
    public function view(User $user, Order $order): bool
    {
        return $user->isAdmin() || $user->id === $order->user_id;
    }

    /**
     * Only customers can place orders.
     */
    public function create(User $user): bool
    {
        return !$user->isAdmin();
    }

    /**
     * Only admins can update order status.
     */
    public function updateStatus(User $user, Order $order): bool
    {
        return $user->isAdmin();
    }
}