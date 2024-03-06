<?php

namespace App\Services;

use App\Config\PrefixKeysCache;
use App\Models\Friend;
use App\Models\FriendRequest;
use App\Models\User;
use App\Notifications\AcceptFrienRequestNotification;
use App\Notifications\FriendRequestNotification;
use App\Traits\CacheTraits;
use App\Traits\ResponseTraits;
use Error;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class UserRelationshipService{

  use ResponseTraits;
  use CacheTraits;
  
  public function __construct(
    private PrefixKeysCache $prefixKeysCache
  ){}

  public function executeQuery(User $user, int $countPaginate = 20, bool $inRandomOrder = false, bool $paginate = true):mixed
  {
    try {
      //Creamos la query
      $query =  
      Friend::where('sender', $user->id)
          ->orWhere('recipient', $user->id)
          ->selectRaw('DISTINCT CASE WHEN sender = ' . $user->id . ' THEN recipient ELSE sender END AS friend_id');

      if($inRandomOrder) $query->inRandomOrder();
      
      //Ejecutamos la consulta

      if($paginate){
        return $query->paginate($countPaginate);
      }else{
        return $query->get();
      }
      
    } catch (\Exception $e) {
      return 'Error al buscar amigos. ' . $e->getMessage();
    }
  }

  public function orderFriend(Collection|array $listFriends):mixed
  {
    if(count($listFriends) < 1) return []; 
      
    $infoFriends = new Collection();

    //Ordenamos la informacion
    foreach($listFriends as $friend){
      $infoFriends->push(
        User::select('id','name','lastname','email','url_image_profile')
        ->where('id',$friend->friend_id)
        ->get()[0]
      );
       
    }

    return $infoFriends;
  }

  public function findFriends(User $user):JsonResponse
  {
    
    $key = (request()->page) 
      ? $this->prefixKeysCache::PREFIXKEYFRIENDSCACHE . $user->id . '/' . request()->page
      : $this->prefixKeysCache::PREFIXKEYFRIENDSCACHE . $user->id;
    
    //Comprobamos si el recurso buscado esta en guardado en memoria cache
    if($this->verifiedKey($key)) return $this->response('',true,200,$this->get($key));
     
    //Ejecutamos la consulta
    $listMyFriends = $this->executeQuery($user);

    if(is_string($listMyFriends)) return $this->serviceUnavailableResponse($listMyFriends);
        
    //Organizamos la informacion de los amigos
    $friendsOrdered = $this->orderFriend($listMyFriends->items());
    //Seteamos la colleccion organizada en la respuesta de base de datos
    $listMyFriends->setCollection($friendsOrdered);

    //Guardamos en cache
    if(count($friendsOrdered) > 1){
      $this->createInfoCache($key, $listMyFriends, now()->addMinutes(5));
    } 

    //Retornamos la respuesta
    return $this->response('success',true,200,$listMyFriends);
    
  }

  public function findUsersToRecommend():JsonResponse
  {
    
    $i= 0;//$i representa la cantidad de veces que se ha llamado a la funcion getUsersToRecommend 

    function getUsersToRecommend($user, $id_friends, $i){
      
      $i++;

      //Obtenemos un conjunto de usuarios de la base de datos
      $possibleRecommendations = 
      User::select('id','name','lastname','email','url_image_profile')
      ->where('id', '!=', $user->id)
      ->inRandomOrder()
      ->take(100)
      ->get();

      $recommendedUsers = [];
      //Clasificamos los usuarios para encontrar recomendados
      foreach($possibleRecommendations as $recommendations){
        if( count($recommendedUsers) > 9 ) break;
        if(!in_array($recommendations->id, $id_friends)) array_push($recommendedUsers,$recommendations);
      }

      //Si no encontramos suficientes recomendados aplicamos recursividad
      if(count($recommendedUsers) < 10 && $i < 10){
        getUsersToRecommend($user,$id_friends,$i);
      }

      return $recommendedUsers;
    }

    try {
      //Obtenemos el usuario logueado
      $user = User::find(Auth::user()->id);

      $key = $this->prefixKeysCache::PREFIXKEYIDFRIENDSCACHE . $user->id;

      if($this->verifiedKey($key)){

        $id_friends = $this->get($key);
        $recommendedUsers = getUsersToRecommend($user, $id_friends,$i);
        return $this->response("",true,200,$recommendedUsers);

      }else{

        //Buscamos los amigos
        $friends = $this->executeQuery($user, 0, false, false);
        //Verificamos si hubo un error durante la busqueda
        if(is_string($friends)) return $this->serviceUnavailableResponse($friends);
        
        //Guardamos los identificadores de los amigos en una variable
        $id_friends = [];
        foreach($friends as $friend){
          array_push($id_friends,$friend->friend_id);
        }

        $this->createInfoCache($key, $id_friends, now()->addMinutes(5));
        
        $recommendedUsers = getUsersToRecommend($user,$id_friends,$i);

        return $this->response("",true,200,$recommendedUsers);
      }  
    } catch (\Exception $e) {
      return $this->response(
        'Error al buscar recomendaciones. ' . $e->getMessage(),
        false,
        500
      );
    } 
  }

  public function findAllMyRequestFriend():JsonResponse
  {
    try {
      $friendRequest = 
          FriendRequest::where('recipient', Auth::user()->id)
          ->where('status', FriendRequest::PENDING)
          ->get();
      return $this->response('',true,200,$friendRequest);
    } catch (\Exception $e) {
      return $this->serviceUnavailableResponse($e->getMessage());
    }
  }

  public function sendRequestFriend(User $recipient)
  {
      try {

        $id_user = Auth::user()->id;

        $friends = 
          Friend::where(function($query) use ($recipient, $id_user) {
          $query->where('sender', $id_user)->where('recipient', $recipient->id);
          })->orWhere(function($query) use ($recipient, $id_user) {
              $query->where('sender', $recipient->id)->where('recipient', $id_user);
          })->get();
        
        //Verificar si el destinatario y el usuario logueado ya son amigos
        if($friends) return $this->response('Ya tienes una relacion de amistad con el usuario id = ' . $recipient->id,true,200);

        return $friends;

        //Verificar si el usuario logueado ya ha realizado una solicitud al destinatario
        $friendRequest = FriendRequest::where('sender',$id_user)
          ->where('recipient',$recipient->id)
          ->where('status',FriendRequest::PENDING)
          ->get();

        //Verificar si el destinatario y el usuario logueado ya son amigos
        if($friendRequest) return $this->response('Ya haz solicitado amistad al usuario id = ' . $recipient->id,true,200);

        //Guardar el registro de la solicitud de amistad
        $request = FriendRequest::create([
          'sender' => $id_user,
          'recipient' => $recipient->id,
          'status' => FriendRequest::PENDING
        ]);

        if(!$request) throw new Error('Error al enviar solicitud');

        //Enviar notificacion al usuario destinatario
        $recipient->notify(new FriendRequestNotification($request));

        return $this->response('solicitud enviada',true,201,$request);

      } catch (\Exception $e) {
        return $this->serviceUnavailableResponse($e->getMessage());
      }
  }

  public function acceptRequestFriend(FriendRequest $friendRequest):JsonResponse
  {
    //Comprobamos si el usuario que acepta la solicitud es el usuario destinario de dicha solicitud
    if(Auth::user()->id != $friendRequest->recipient) return $this->unauthorizedResponse();

    // Empezar una transacción
    DB::beginTransaction();
      try {
        
        //Modifica el status de friendRequest
        $friendRequest->status = FriendRequest::ACCEPTED;
        $requestUpdated = $friendRequest->save();


        if(!$requestUpdated){
          // Si no se pudo crear el post, lanzar una excepción
          throw new \Exception('Error updating FriendRequest');
        }

        //Guardar el registro de la amistad
        $friends = Friend::create([
          'sender' => $friendRequest->sender,
          'recipient' => $friendRequest->recipient
        ]);

        if(!$friends){
          // Si no se pudo crear el post, lanzar una excepción
          throw new \Exception('Error creating friend record');
        }

        //Enviar notificacion de aceptacion de solicitud de amistad al usuario que envio la solicitud
        $sender = User::find($friendRequest->sender);
        $sender->notify(new AcceptFrienRequestNotification($friends)); 

          // Commit de la transacción si todo ha ido bien
        DB::commit();

        return $this->response('solicitud aceptada',true,201,$friends);


      } catch (\Exception $e) {
        // Rollback de la transacción en caso de error
        DB::rollBack();
        return $this->serviceUnavailableResponse($e->getMessage());
      }
  }

  public function destroyFriend(User $user)
  {
    try {
      //code...
    } catch (\Throwable $th) {
      //throw $th;
    }
  }

}