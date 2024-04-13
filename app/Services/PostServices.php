<?php

namespace App\Services;

use App\Http\Requests\PostRequest;
use App\Models\Post;
use App\Models\User;
use App\Notifications\PostNotification;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use App\Traits\ResponseTraits;
use Exception;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class PostServices
{

use ResponseTraits;
use AuthorizesRequests;

  public function __construct(
    private ImageServices $imageServices,
    private UserServices $userServices
  ){}

  public function index(User $user)
  {
    try {
      $posts = Post::where('user_id', $user->id)->paginate(30);
      return response()->json([
          'status' => true,
          'posts' => $posts
      ],200);
    } catch (\Exception $e) {
      //Retornamos error al cliente
      $response = $this->response(
        'Error Searching Posts.' . " " . $e->getMessage(), 
        false,
        404,
      );
      return $response;
    }
  }

  public function createPost(PostRequest $request)
  {
    //Creamos el post
    $post = Post::create([
      'description' => $request->description,
      'is_edit' => Post::UNEDITED,
      'user_id' => Auth::user()->id
    ]);

    return $post;
  }
  
  public function controlProcessPost(PostRequest $request, int $optionsImage = 0)
  {
    // Empezar una transacción
    DB::beginTransaction();

    try {
        // Creamos el post
        $post = $this->createPost($request);

        if (!$post) {
          // Si no se pudo crear el post, lanzar una excepción
          throw new \Exception('Error creating post');
        }

        $filename = null;
        // Comprobamos si hay imágenes para el post
        if ($request->hasFile('image')) {

          // Guardamos la imagen en el directorio de imagenes
          $filename = $this->imageServices->move($request->file('image'), $post);
          //Guardamos la url de la imagen en la base de datos
          $image = $this->imageServices->createImage($filename, $post);

          if (!$image) {
            // Si no se pudo crear la imagen, lanzar una excepción
            throw new \Exception('Error creating image');
          }

          if($optionsImage != 0){

            $urlImageUser = $this->userServices->assignedUserImage($optionsImage, $image->url);

            if(is_string($urlImageUser)){
              // Si no se pudo crear la imagen, lanzar una excepción
              throw new \Exception($urlImageUser);
            }
          }
        }

        //Generamos la notificacion del post
        auth()->user()->notify(new PostNotification($post));
        
        // Commit de la transacción si todo ha ido bien
        DB::commit();

        return response()->json([
          'status' => true,
          'data' => Post::with('images')->where('id', $post->id)->get(),
          'message' => 'Post created successfully'
        ],201);
               
    } catch (\Exception $e) {
        // Rollback de la transacción en caso de error
        DB::rollBack();

        $errorMessage = $e->getMessage();

        //Se borra la imagen del servidor
        if (isset($filename)) {
          Storage::delete('public/images/' . "" .$post->user->name . "" . $post->user_id . "/". $filename);
        }

        return $this->serviceUnavailableResponse($errorMessage);
    }
  }

  public function update(PostRequest $request, Post $post)
  {
    try {

      $this->authorize('update',$post);
      
      $post->description = $request->description;
      $post->is_edit = Post::EDITED;
      $postUpdated = $post->save();

      if(!$postUpdated) throw new Exception('Error updating Post');

      //Retornamos respuesta al cliente
      return $this->response(
        'Post updated successfully', 
        true,
        200, 
      );

    } catch (\Exception $e) {
      
      //Retornamos error al cliente
      return $this->serviceUnavailableResponse($e->getMessage());
    }
  }

  public function destroy(Post $post)
  {
    try {

      $this->authorize('delete',$post);

      $postDeleted = $post->delete();
      if(!$postDeleted) throw new Exception('Error deleting Post');

      //Retornamos respuesta al cliente
      return $this->response(
        'Post deleted successfully', 
        true,
        200, 
      );

    } catch (\Exception $e) {
      
      //Retornamos error al cliente
      return $this->serviceUnavailableResponse($e->getMessage());
    }
  }
}