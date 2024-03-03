<?php

namespace App\Services;

use App\Http\Requests\UserRequest;
use App\Models\Friend;
use App\Models\User;
use App\Traits\ResponseTraits;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class UserServices
{

  use ResponseTraits;

  public function index()
  {
    $user = Auth::user();
    return response()->json([
        'status' => true,
        'user' => $user,
    ],200);
  } 

  public function create(UserRequest $request)
  {
    try {

      $user = User::create([
        'name' => $request->name,
        'lastname' => $request->lastname,
        'email' => $request->email,
        'password' => Hash::make($request->password)
      ]);
  
      //Retornamos respuesta al cliente
      $response = $this->response(
        'User created successfully', 
        true,
        201,
        $user, 
      );

      return $response;

    } catch (\Exception $e) {
      //Retornamos error al cliente
      $response = $this->response(
        'Error creating user.' . " " . $e->getMessage(), 
        false,
        404,
      );

      return $response;
    }
  }

  public function updateField(UserRequest $request, User $user)
  {
      
    try {

      //Comprobamos si el usuario que se quiere editar es el usuario logueado
      if(Auth::user()->id != $user->user_id) return response()->json([
        'status' => false,
        'message' => 'Not authorized'
      ],403);


      //Guardamos el campo de la solicitud en una coleccion
      $fields = $request->input();
      //Guardamos la key del  campo a actualizar
      $keyField = array_keys($fields)[0];
      //Asignamos nuevo valor al campo solicitado
      $user->$keyField = $fields[$keyField];
      //Guardamos cambios
      $user->save();

      //Retornamos respuesta al cliente
      $response = $this->response(
        'User updated successfully', 
        true,
        200,
        $user, 
      );

      return $response;

    } catch (\Exception $e) {

      //Retornamos error al cliente
      $response = $this->response(
        'Error updating user.' . " " . $e->getMessage(), 
        false,
        404,
      );

      return $response;
    }
    
  }

  public function destroy(User $user)
  {
    try {
      //Comprobamos si el usuario que se quiere eliminar es el usuario logueado
      if(Auth::user()->id != $user->user_id) return response()->json([
        'status' => false,
        'message' => 'Not authorized'
      ],403);

      $user->delete();

      //Retornamos respuesta al cliente
      $response = $this->response(
        'User deleted successfully', 
        true,
        200, 
      );

      return $response;

    } catch (\Exception $e) {
      //Retornamos error al cliente
      $response = $this->response(
        'Error deleting user.' . " " . $e->getMessage(), 
        false,
        404,
      );

      return $response;
    }
  }

  public function updateImageProfile(string $urlImage)
  {
    $user = User::find(Auth()->user()->id);
    $user->url_image_profile = $urlImage;
    $user->save();
    return $user;
  }

  public function updateImageCover(string $urlImage)
  {
    $user = User::find(Auth()->user()->id);
    $user->url_image_cover = $urlImage;
    $user->save();
    return $user;
  }

  public function allFriend(User $user, int $countPaginate = 20, int $inRandomOrder = 0):mixed
  {
    try {
      //Realizamos la consulta
      $query =  
      Friend::where('sender', $user->id)
          ->orWhere('recipient', $user->id)
          ->selectRaw('DISTINCT CASE WHEN sender = ' . $user->id . ' THEN recipient ELSE sender END AS friend_id');

      if($inRandomOrder == 1) $query->inRandomOrder();

      $listFriends = $query->paginate($countPaginate);
          
      if(count($listFriends) < 1) return []; 
      
      $infoFriends = [];

      //Ordenamos la informacion
      foreach($listFriends as $friend){
        array_push($infoFriends, User::select('id','name','lastname','email','url_image_profile')->where('id',$friend->friend_id)->get()[0]);
      }

      return $infoFriends;

    } catch (\Exception $e) {
      return 'Error al buscar amigos. ' . $e->getMessage();
    }
  }

  public function findAllFriends(User $user)
  {
    $friends = $this->allFriend($user);
    if(!is_array($friends)) return $this->response($friends,false,500);
    return $this->response('',true,200,$friends); 
  }

  public function findUsersToRecommend():mixed 
  {
    try {
      //Obtenemos el usuario logueado
      $user = User::find(Auth::user()->id);
      //Buscamos los amigos
      $friends = $this->allFriend($user, 50);
      //Verificamos si hubo un error durante la busqueda
      if(!is_array($friends)) return $this->response($friends,false,500);
      //Guardamos los identificadores de los amigos en una variable
      $id_friends = array_column($friends, 'id');
      //Obtenemos un conjunto de usuarios de la base de datos
      $possibleRecommendations = 
        User::select('id','name','lastname','email','url_image_profile')
        ->where('id', '!=', $user->id)
        ->inRandomOrder()
        ->take(30)
        ->get();

      $recommendedUsers = [];

      foreach($possibleRecommendations as $recommendations){
        if(!in_array($recommendations->id,$id_friends)) array_push($recommendedUsers,$recommendations);
        if(count($recommendedUsers) > 4) break;
      }

      return $this->response("",true,200,$recommendedUsers);
    } catch (\Exception $e) {
      return $this->response(
        'Error al buscar recomendaciones. ' . $e->getMessage(),
        false,
        500
      );
    } 
  }
}