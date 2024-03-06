<?php

namespace App\Services;

use App\Http\Requests\CommentRequest;
use App\Models\Comment;
use App\Models\Post;
use Illuminate\Support\Facades\Auth;
use App\Traits\ResponseTraits;
use Error;

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

      if(!$newComment) throw new Error('Error creating comment');

      //Retornamos respuesta al cliente
      return $this->response(
        'Comment created successfully', 
        true,
        201,
        $newComment, 
      );

    } catch (\Exception $e) {
      
      //Retornamos error al cliente
      return $this->serviceUnavailableResponse(
        $e->getMessage(), 
      );
    }
  }
  
  public function update(CommentRequest $request, Comment $comment)
  {   
    try{

      //Comprobamos si el comentario que se quiere editar pertenece al usuario logueado
      if(Auth::user()->id != $comment->user_id) return $this->unauthorizedResponse();
        
      $comment->paragraph = $request->paragraph;
      $comment->is_edit = Comment::EDITED;
      $commentUpdated = $comment->save();

      if(!$commentUpdated) throw new Error('Error updating comment');

      //Retornamos respuesta al cliente
      return $this->response(
        'Comment updated successfully', 
        true,
        201,
        $comment
      );

    }catch(\Exception $e){

      //Retornamos error al cliente
      //Retornamos error al cliente
      return $this->serviceUnavailableResponse(
        $e->getMessage(), 
      );

    }
  }

  public function destroy(Comment $comment)
  {   
    try{
      //Comprobamos si el comentario que se quiere editar pertenece al usuario logueado
      if(Auth::user()->id != $comment->user_id) return $this->unauthorizedResponse();

      $commentDeleted = $comment->delete();
      if(!$commentDeleted) throw new Error('Error deleting comment');

      //Retornamos respuesta al cliente
      return $this->response(
        'Comment deleted successfully', 
        true,
        200, 
      );

    }catch(\Exception $e){
      
      //Retornamos error al cliente
      return $this->serviceUnavailableResponse(
        $e->getMessage(), 
      );

    }
  }
}