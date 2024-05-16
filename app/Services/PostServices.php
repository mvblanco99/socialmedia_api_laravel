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

  public function index(User $user, int $paginate)
  {
    try {
      $posts = Post::with(['images', 'user'])
        ->where('user_id',$user->id)
        ->orderBy('id', 'desc')
        ->paginate($paginate);

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

        $filenames = [];
        $imagesUploadedBD = [];

        // Comprobamos si hay imágenes para el post
        if ($request->hasFile('images')) {

          $images = $request->file('images');

          foreach($images as $image){
            // Guardamos la imagen en el directorio de imagenes
            array_push($filenames,$this->imageServices->move($image, $post->user_id));
          }

          foreach($filenames as $filename){
            //Guardamos la url de la imagen en la base de datos
            array_keys($imagesUploadedBD,$this->imageServices->createImage($filename, $post));
          }

          if (count($imagesUploadedBD)> 0) {
            foreach($imagesUploadedBD as $img){
              if(!$img){
                // Si no se pudo crear la imagen, lanzar una excepción
                throw new \Exception('Error creating image');
              }
            }
            
          }
        }

        //Generamos la notificacion del post
        // auth()->user()->notify(new PostNotification($post));
        
        // Commit de la transacción si todo ha ido bien
        DB::commit();

        return response()->json(Post::with('images')->with('user')->where('id', $post->id)->get(),201);
               
    } catch (\Exception $e) {
        // Rollback de la transacción en caso de error
        DB::rollBack();

        //Se borra la imagen del servidor
        if (isset($filename)) {
          foreach($filenames as $filename){
            Storage::delete('public/images/' . "" .$post->user->name . "" . $post->user_id . "/". $filename);
          }
        }

        return response()->json(['message' => $e->getMessage()],500);
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