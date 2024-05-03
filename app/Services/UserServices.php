<?php

namespace App\Services;

use App\Http\Requests\UpdateFieldUserRequest;
use App\Http\Requests\UpdateImageUserRequest;
use App\Models\Post;
use App\Models\User;
use App\Traits\ResponseTraits;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class UserServices
{
  use ResponseTraits;
  use AuthorizesRequests;

  public function __construct(
    private ImageServices $imageServices
  ){}

  public function index():JsonResponse
  {
    $user = Auth::user();
    return response()->json($user,200);
  }
  
  public function findUser($user):JsonResponse
  {
    try {
      $data = User::find($user);
      if(!$data) return response()->json(['message' => 'User not found'],200);
      return response()->json($data,200);
    } catch (Exception $e) {
      return response()->json(['message' => $e->getMessage()],500);
    }
  }

  public function updateField(UpdateFieldUserRequest $request, string $user_id)
  {
    try {
      $user = User::find($user_id);
      //Comprobamos que el usuario tiene permisos para realizar la accion
      $this->authorize('update', $user);

      //Guardamos el campo de la solicitud en un array
      $fields = $request->input();
      //Guardamos la key del  campo a actualizar
      $keyField = array_keys($fields)[0];
      //Asignamos nuevo valor al campo solicitado
      $user->$keyField = $fields[$keyField];
      //Guardamos cambios
      $userUpdated = $user->save();

      if(!$userUpdated)  throw new Exception('Error updating field ' . $keyField . ' of the user');

      //Retornamos respuesta al cliente
      return response()->json(User::find($user_id),200);
      
    } catch (\Exception $e) {
      //Retornamos error al cliente
      return response()->json(['message' => $e->getMessage()],500);
    }
  }

  public function updateImage(UpdateImageUserRequest $request, string $user_id, string $type_image):JsonResponse
  {
    // Empezar una transacción
    DB::beginTransaction();
    try {

      //Comprobamos que el usuario tiene permisos para realizar la accion
      $this->authorize('update', User::find($user_id));
      
      //Guardar la imagen en el directorio correspondiente
      $filename = $this->imageServices->move($request->file('image'),$user_id);
      
      //Crear el post en la bd
      $post  = Post::create([
        'description' => $request->description,
        'is_edit' => Post::UNEDITED,
        'user_id' => $user_id
      ]);
      
      // Si no se pudo crear el post, lanzar una excepción
      if (!$post) throw new \Exception('Error creating post');
      
      //Guardar la url de la imagen la bd
      $newImage = $this->imageServices->createImage($filename,$post);

      // Si no se pudo crear la imagen, lanzar una excepción
      if (!$newImage) throw new \Exception('Error creating image');
      
      //Actualizar la url de la imagen del usuario
      $post->user->$type_image = $newImage->url;
      $assignedUserImage = $post->user->save();

      // Si no se pudo actualizar la url de la imagen( Profile o Cover ) del usuario, lanzar una excepción
      if (!$assignedUserImage) throw new \Exception('Error assigned image user');

      // Commit de la transacción si todo ha ido bien
      DB::commit();

      //Retornamos el post
      return response()->json(Post::with('images')->with('user')->where('id', $post->id)->get(),201);

    } catch (\Illuminate\Auth\Access\AuthorizationException $e) {
     
      //Retornamos el mensaje de error 
      return response()->json(['message' => $e->getMessage()], 403);
    
    } catch (\Exception $e) {
      
      // Rollback de la transacción en caso de error
      DB::rollBack();
      
      //Se borra la imagen del servidor
      if (isset($filename)) 
        Storage::delete('public/images/' . "" .$post->user->name . "" . $post->user_id . "/". $filename);

      //Retornamos el mensaje de error 
      return response()->json(['message' => $e->getMessage()], 500);
    }
  }

  public function destroy(string $user_id):JsonResponse
  {
    try {
      $user = User::find($user_id);
      //Comprobamos que el usuario tiene permisos para realizar la accion
      $this->authorize('delete', $user);
      
      //Eliminamos el usuario
      $userDeleted = $user->delete();
      if(!$userDeleted) throw new Exception('Error deleting user');

      //Retornamos respuesta al cliente
      return response()->json(204);

    } catch (\Illuminate\Auth\Access\AuthorizationException $e) {
      return response()->json(['message' => $e->getMessage()],403);
    }catch (\Exception $e) {
      return response()->json(['message' => $e->getMessage()],500);
    }
  }

  public function getImagesUser(string $user_id):JsonResponse
  {
    try {
      $user = User::find($user_id);
      if(!$user) return response()->json(['message' => 'User not found'],200);
      $posts = Post::with('images')->whereHas('images')->where('user_id',$user_id)->paginate(24);
      return response()->json($posts,200);
    } catch (\Exception $e) {
      return response()->json(['message' => $e->getMessage()],500);
    }
  }

}

