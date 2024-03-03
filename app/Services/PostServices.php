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
use Illuminate\Support\Facades\Notification;
use Psy\VersionUpdater\SelfUpdate;

class PostServices
{

use ResponseTraits;

  private const NONE = 0;
  private const ADDIMAGEPROFILE = 1;
  private const ADDIMAGECOVER = 2;

  public function __construct(
    private ImageServices $imageServices,
    private UserServices $userServices
  ){}

  public function index(User $user)
  {
    try {
      $posts = Post::where('user_id', $user->id)->paginate(10);
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
  
  public function assignedUserImage(int $optionsImage, string $url_image)
  {
    $urlImageUser = null;

    if($optionsImage == self::ADDIMAGEPROFILE){
      $urlImageUser = $this->userServices->updateImageProfile($url_image);
     }else if($optionsImage == self::ADDIMAGECOVER){
      $urlImageUser = $this->userServices->updateImageCover($url_image);
    }

    return $urlImageUser;
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

          //Comprobamos si el usuario quiere asignar la imagen como perfil o portada
          if($optionsImage != $this::NONE){
            
            $urlImageUser = $this->assignedUserImage($optionsImage, $image->url);
            
            if(!$urlImageUser){
              // Si no se pudo crear la imagen, lanzar una excepción
              throw new \Exception('Error asignando image user');
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

        return response()->json([
          'status' => false,
          'message' => $errorMessage,
        ],500);
    }
  }

  public function update(PostRequest $request, Post $post)
  {
    try {

      //Comprobamos si el post que se quiere editar pertenece al usuario logueado
      if(Auth::user()->id == $post->user_id) return response()->json([
        'status' => false,
        'message' => 'Not authorized'
      ],403);

      $post->paragraph = $request->paragraph;
      $post->is_edit = Post::EDITED;
      $post->save();

      //Retornamos respuesta al cliente
      return $this->response(
        'Post updated successfully', 
        true,
        200, 
      );

    } catch (\Exception $e) {
      
      //Retornamos error al cliente
      $response = $this->response(
        'Error updating Post.' . " " . $e->getMessage(), 
        false,
        500,
      );

      return $response;
    }
  }

  public function destroy(Post $post)
  {
    try {

      //Comprobamos si el post que se quiere editar pertenece al usuario logueado
      if(Auth::user()->id == $post->user_id) return response()->json([
        'status' => false,
        'message' => 'Not authorized'
      ],403);

      $post->delete();

      //Retornamos respuesta al cliente
      return $this->response(
        'Post deleted successfully', 
        true,
        200, 
      );

    } catch (\Exception $e) {
      
      //Retornamos error al cliente
      $response = $this->response(
        'Error deleting Post.' . " " . $e->getMessage(), 
        false,
        500,
      );

      return $response;
    }
  }
}