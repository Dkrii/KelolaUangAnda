<?php

namespace App\Policies;

use App\Models\SavingGoal;
use App\Models\User;

class SavingGoalPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, SavingGoal $savingGoal): bool
    {
        return $savingGoal->user_id === $user->id;
    }

    public function create(User $user): bool
    {
        return true;
    }

    public function update(User $user, SavingGoal $savingGoal): bool
    {
        return $savingGoal->user_id === $user->id;
    }

    public function delete(User $user, SavingGoal $savingGoal): bool
    {
        return $savingGoal->user_id === $user->id;
    }

    public function contribute(User $user, SavingGoal $savingGoal): bool
    {
        return $savingGoal->user_id === $user->id;
    }
}
