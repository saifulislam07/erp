<?php

namespace App\Policies;

use App\Models\Account;
use App\Models\User;

class AccountPolicy
{
    /**
     * Determine whether the user can view any models (payable/receivable ledgers).
     */
    public function viewAny(User $user): bool
    {
        return $user->is_admin || $user->hasRole('Accountant') || $user->can('cash.view');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Account $account): bool
    {
        return $this->viewAny($user);
    }

    /**
     * Determine whether the user can create models. Only Admin may create
     * manual payable/receivable entries.
     */
    public function create(User $user): bool
    {
        return $user->is_admin;
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Account $account): bool
    {
        return $user->is_admin;
    }

    /**
     * Determine whether the user can delete the model. Accountants may never
     * delete payable/receivable entries.
     */
    public function delete(User $user, Account $account): bool
    {
        return $user->is_admin;
    }

    /**
     * Determine whether the user can settle the model.
     */
    public function settle(User $user, Account $account): bool
    {
        return $user->is_admin;
    }
}
