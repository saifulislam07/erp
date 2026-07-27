<?php

namespace App\Policies;

use App\Models\Order;
use App\Models\User;

class OrderPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->is_admin || $user->hasRole('Store Manager') || $user->can('order.view');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Order $order): bool
    {
        return $this->viewAny($user);
    }

    /**
     * Determine whether the user can accept/reject/update the status of the model.
     */
    public function update(User $user, Order $order): bool
    {
        return $user->is_admin || $user->can('order.edit');
    }

    /**
     * Determine whether the user can dispatch the order from a store.
     */
    public function dispatch(User $user, Order $order): bool
    {
        return $user->is_admin || $user->hasRole('Store Manager') || $user->can('order.edit');
    }
}
