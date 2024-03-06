<?php

namespace App\Services;

use App\Http\Requests\UserRequest;
use App\Models\User;
use App\Traits\ResponseTraits;
use Error;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class UserServices
{
  use ResponseTraits;

  private const ADDIMAGEPROFILE = 1;
  private const ADDIMAGECOVER = 2;

  public function index():JsonResponse
  {
    $user = Auth::user();
    return response()->json([
        'status' => true,
        'user' => $user,
    ],200);
  } 

  public function create(UserRequest $request):JsonResponse
  {
    try {

      $user = User::create([
        'name' => $request->name,
        'lastname' => $request->lastname,
        'email' => $request->email,
        'password' => Hash::make($request->password)
      ]);

      if(!$user) throw new Error('Error creating user');
  
      //Retornamos respuesta al cliente
      return $this->response(
        'User created successfully', 
        true,
        201,
        $user, 
      );
    } catch (\Exception $e) {
      //Retornamos error al cliente
      return $this->response(
        $e->getMessage(), 
        false,
        500,
      );
    }
  }

  public function updateField(UserRequest $request, User $user):JsonResponse
  {
    try {
      //Comprobamos si el usuario que se quiere editar es el usuario logueado
      if(Auth::user()->id != $user->user_id) return $this->unauthorizedResponse();

      //Guardamos el campo de la solicitud en un array
      $fields = $request->input();
      //Guardamos la key del  campo a actualizar
      $keyField = array_keys($fields)[0];
      //Asignamos nuevo valor al campo solicitado
      $user->$keyField = $fields[$keyField];
      //Guardamos cambios
      $userUpdated = $user->save();

      if(!$userUpdated)  throw new Error('Error updating field ' . $keyField . ' of the user');

      //Retornamos respuesta al cliente
      return $this->response(
        'User updated successfully', 
        true,
        200,
        $user, 
      );
    } catch (\Exception $e) {
      //Retornamos error al cliente
      $this->response(
        $e->getMessage(), 
        false,
        500,
      );
    }
  }

  public function destroy(User $user):JsonResponse
  {
    try {
      //Comprobamos si el usuario que se quiere eliminar es el usuario logueado
      if(Auth::user()->id != $user->user_id) return $this->unauthorizedResponse();
      //Eliminamos el usuario
      $userDeleted = $user->delete();

      if(!$userDeleted) throw new Error('Error deleting user');

      //Retornamos respuesta al cliente
      return $this->response(
        'User deleted successfully', 
        true,
        200, 
      );
    } catch (\Exception $e) {
      //Retornamos error al cliente
      return $this->response(
        $e->getMessage(), 
        false,
        500,
      );
    }
  }

 public function assignedUserImage(int $optionsImage = 0, string $urlImage):mixed
  {
    try {

      if($optionsImage < self::ADDIMAGEPROFILE 
      || $optionsImage > self::ADDIMAGECOVER ) throw new \Exception('Choose image invalid');

      $user = User::find(Auth()->user()->id);

      if($optionsImage == self::ADDIMAGEPROFILE){
        $user->url_image_profile = $urlImage;
      }else{
        $user->url_image_cover = $urlImage;
      }
        
      $userUpdated = $user->save();

      if(!$userUpdated) throw new \Exception('Error updating image of the user');

    } catch (\Exception $e) {
      return $e->getMessage();
    }
  }
}

