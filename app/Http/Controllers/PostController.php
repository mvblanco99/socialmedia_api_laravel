<?php

namespace App\Http\Controllers;

use App\Http\Requests\PostRequest;
use App\Models\Post;
use App\Models\User;
use App\Notifications\PostNotification;
use App\Services\PostServices;

class PostController extends Controller
{

    public function __construct(
        private PostServices $postServices, 
    ){}

    public function index(User $user)
    {
        $posts = $this->postServices->index($user);
        return $posts;
    }

    public function selectPostsToDisplayOnHome(User $user){}

    public function store(PostRequest $request)
    {
       $response = $this->postServices->controlProcessPost($request);
       return $response;
    }

    public function update(PostRequest $request, Post $post)
    {
        $response = $this->postServices->update($request, $post);
        return $response;
    }

    public function destroy(Post $post)
    {
        $response = $this->postServices->destroy($post);
        return $response;
    }
}
