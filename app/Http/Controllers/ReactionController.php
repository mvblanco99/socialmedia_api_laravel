<?php

namespace App\Http\Controllers;

use App\Models\Reaction;
use App\Models\User;
use App\Services\ReactionServices;

class ReactionController extends Controller
{
    
    public function __construct(
        private ReactionServices $reactionServices
    ){}

    public function store(User $user, int $idRecord, int $model)
    {
        $response = $this->reactionServices->store($user, $idRecord, $model);
        return $response;
    }

    public function destroy(Reaction $reaction)
    {
        $response = $this->reactionServices->destroy($reaction);
        return $response;
    }
}
