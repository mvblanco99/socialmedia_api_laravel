<?php

namespace App\Http\Controllers;

use App\Http\Requests\CommentRequest;
use App\Models\Comment;
use App\Services\CommentServices;

class CommentController extends Controller
{   

    public function __construct(
        private CommentServices $commentService
    ) {}

    public function index(){}

    public function store(CommentRequest $request, int $idRecord ,int $model)
    {
       $response = $this->commentService->store($request, $idRecord, $model);
       return $response;
    }

    public function update(CommentRequest $request, Comment $comment)
    {   
        $response = $this->commentService->update($request, $comment);
        return $response;
    }

    public function destroy(Comment $comment)
    {
        $response = $this->commentService->destroy($comment);
        return $response;
    }
}
