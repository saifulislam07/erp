<?php

namespace App\Policies;

use App\Models\Sale;
use App\Models\User;

class SalePolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->is_admin || $user->hasAnyRole(['Employee', 'Local Seller', 'Accountant']) || $user->can('sale.view');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Sale $sale): bool
    {
        return $this->viewAny($user);
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->is_admin || $user->hasAnyRole(['Employee', 'Local Seller']) || $user->can('sale.create');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Sale $sale): bool
    {
        return $user->is_admin || $user->can('sale.edit');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Sale $sale): bool
    {
        return $user->is_admin;
    }

    /**
     * Determine whether the user can sell to a client/agent customer.
     */
    public function sellToClientAgent(User $user): bool
    {
        return $user->is_admin || $user->can('sale.client_agent');
    }

    /**
     * Determine whether the user can apply an extra line-item discount.
     */
    public function applyDiscount(User $user): bool
    {
        return $user->is_admin || $user->can('sale.discount');
    }
}
