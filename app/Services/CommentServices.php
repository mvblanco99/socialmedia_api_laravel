<?php

namespace App\Services;

use App\Http\Requests\CommentRequest;
use App\Models\Comment;
use App\Models\Post;
use Illuminate\Support\Facades\Auth;
use App\Traits\ResponseTraits;

class CommentServices
{

  use ResponseTraits;
  
  public function selectModeltoComment(int $modelSelected)
  {
    $modelsAvailable = [
        1 => new Comment,
        2 => new Post,
    ];
    return $modelsAvailable[$modelSelected];
  }

  public function store(CommentRequest $request, int $id, int $model)
  {

    try {
      //Obtenemos el modelo que se va a utilizar para comentar
      $modelSelected = $this->selectModeltoComment($model);
      
      //Buscamos la informacion del registro del modelo elegido
      $record = $modelSelected::find($id);

      //Creamos el comentario
      $newComment = $record->comment()->create([
        'paragraph' => $request->paragraph,
        'is_edit' => Comment::UNEDITED,
        'user_id' =>  Auth::user()->id
      ]);

      //Retornamos respuesta al cliente
      $response = $this->response(
        'Comment created successfully', 
        true,
        201,
        $newComment, 
      );

      return $response;

    } catch (\Exception $e) {
      
      //Retornamos error al cliente
      $response = $this->response(
        'Error creating comment.' . " " . $e->getMessage(), 
        false,
        500,
      );

      return $response;
    }
  }
  
  public function update(CommentRequest $request, Comment $comment)
  {   
    try{

      //Comprobamos si el comentario que se quiere editar pertenece al usuario logueado
      if(Auth::user()->id != $comment->user_id) return response()->json([
        'status' => false,
        'message' => 'Not authorized'
      ],403);
        
      $comment->paragraph = $request->paragraph;
      $comment->is_edit = Comment::EDITED;
      $comment->save();

      //Retornamos respuesta al cliente
      $response = $this->response(
        'Comment updated successfully', 
        true,
        201,
        $comment
      );

      return $response;

    }catch(\Exception $e){

      //Retornamos error al cliente
      $response = $this->response(
        'Error updating comment.' . " " . $e->getMessage(), 
        false,
        500,
      );

      return $response;

    }
  }

  public function destroy(Comment $comment)
  {   
    try{
      //Comprobamos si el comentario que se quiere editar pertenece al usuario logueado
      if(Auth::user()->id != $comment->user_id) return response()->json([
        'status' => false,
        'message' => 'Not authorized'
      ],403);

      $comment->delete();

      //Retornamos respuesta al cliente
      $response = $this->response(
        'Comment deleted successfully', 
        true,
        200, 
      );

      return $response;

    }catch(\Exception $e){
      
      //Retornamos error al cliente
      $response = $this->response(
        'Error deleting comment.' . " " . $e->getMessage(), 
        false,
        500,
      );

      return $response;

    }
  }
}