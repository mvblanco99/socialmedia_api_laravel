<?php

namespace App\Services;

use App\Models\Comment;
use App\Models\Post;
use App\Models\Reaction;
use App\Models\User;
use App\Traits\ResponseTraits;
use Illuminate\Support\Facades\Auth;

class ReactionServices
{

  use ResponseTraits;

  public function selectModeltoReaction(int $modelSelected)
  {
    $modelsAvailable = [
        1 => new Comment,
        2 => new Post,
    ];
    return $modelsAvailable[$modelSelected];
  }

  public function store(User $user, int $id, int $model)
  {

    try {
      //Obtenemos el modelo que se va a utilizar para comentar
      $modelSelected = $this->selectModeltoReaction($model);
      
      //Buscamos la informacion del registro del modelo elegido
      $record = $modelSelected::find($id);

      //Creamos el comentario
      $newReaction = $record->reaction()->create([
        'user_id' =>  $user->id
      ]);

      //Retornamos respuesta al cliente
      $response = $this->response(
        'Reaction created successfully', 
        true,
        201,
        $newReaction, 
      );

      return $response;

    } catch (\Exception $e) {
      
      //Retornamos error al cliente
      $response = $this->response(
        'Error creating reaction.' . " " . $e->getMessage(), 
        false,
        500,
      );

      return $response;
    }
  }

  public function destroy(Reaction $reaction)
  {   
    try{

      //Comprobamos si la reaccion que se quiere eliminar pertenece al usuario logueado
      if(Auth::user()->id != $reaction->user_id) return response()->json([
        'status' => false,
        'message' => 'Not authorized'
      ],403);

      $reaction->delete();

      //Retornamos respuesta al cliente
      $response = $this->response(
        'Reaction deleted successfully', 
        true,
        200, 
      );

      return $response;

    }catch(\Exception $e){
      
      //Retornamos error al cliente
      $response = $this->response(
        'Error deleting reaction.' . " " . $e->getMessage(), 
        false,
        500,
      );

      return $response;

    }
  }


}