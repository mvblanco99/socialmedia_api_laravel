<?php

namespace App\Policies;

use App\Models\Reaction;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class ReactionPolicy
{
    public function delete(User $user, Reaction $reaction): bool
    {
        return $user->id === $reaction->user_id;
    }
}
