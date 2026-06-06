<?php

namespace App\Policies;

use App\Models\Borrowing;
use App\Models\Member;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class BorrowingPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
         //return $user->role === 'admin' || $member->user_id === $user->id;
          return true;
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Borrowing $borrowing): bool
    {
           return $user->isAdmin() || $borrowing->member->user_id === $user->id;
    }


    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return false;
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Borrowing $borrowing): bool
    {
        return false;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Borrowing $borrowing): bool
    {
        return false;
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Borrowing $borrowing): bool
    {
        return false;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Borrowing $borrowing): bool
    {
        return false;
    }


     // Member يرجع كتابه بس، Admin يرجع أي كتاب
    public function returnBook(User $user, Borrowing $borrowing): bool
    {
        if ($user->role === 'admin') {
            return true;
        }

        if ($user->role === 'member') {
            return $borrowing->member->user_id === $user->id;
        }

        return false;
    }


    // Member يجدد كتابه بس، Admin يجدد أي كتاب
    public function renew(User $user, Borrowing $borrowing): bool
    {
        if ($user->role === 'admin') {
            return true;
        }

        if ($user->role === 'member') {
            return $borrowing->member->user_id === $user->id;
        }

        return false;
    }

}
