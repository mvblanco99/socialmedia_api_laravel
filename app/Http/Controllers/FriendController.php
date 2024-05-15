<?php

namespace App\Http\Controllers;

use App\Models\FriendRequest;
use App\Models\User;
use App\Services\UserRelationshipService;

class FriendController extends Controller
{
    public function __construct(
        private UserRelationshipService $UserRelationshipService
    ){}
    
    public function findUsersToRecommend(){
        $response = $this->UserRelationshipService->findUsersToRecommend();
        return $response;
    }

    public function findFriends(User $user){
        $response = $this->UserRelationshipService->findFriends($user);
        return $response;
    }

    public function verifyFriendshipRelationship(string $user)
    {
        $response = $this->UserRelationshipService->verifyFriendshipRelationship($user);
        return $response;
    }

    public function findAllMyRequestFriend()
    {
        return $this->UserRelationshipService->findAllMyRequestFriend();
    }

    public function sendRequestFriend(User $recipient)
    {
        return $this->UserRelationshipService->sendRequestFriend($recipient);
    }

    public function acceptRequestFriend(FriendRequest $friendRequest)
    {
        return $this->UserRelationshipService->acceptRequestFriend($friendRequest);
    }

    public function destroyFriendshipRelationship(User $user)
    {
        return $this->UserRelationshipService->destroyFriendshipRelationship($user);
    }
}
