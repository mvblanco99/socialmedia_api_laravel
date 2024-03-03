<?php

namespace App\Http\Controllers;

use App\Http\Requests\PostRequest;
use App\Http\Requests\UserRequest;
use App\Models\Friend;
use App\Models\FriendRequest;
use App\Models\User;
use App\Notifications\AcceptFrienRequestNotification;
use App\Notifications\FriendRequestNotification;
use App\Services\PostServices;
use App\Services\UserServices;
use Illuminate\Support\Facades\Auth;

class UserController extends Controller
{

    public function __construct(
        private PostServices $postServices,
        private UserServices $userServices 
    ){}
    
    public  function index()
    {
        //extraemos los datos del usuario autenticado
        $response = $this->userServices->index();
        return $response;
    }

    public function register(UserRequest $request)
    {
        $response = $this->userServices->create($request);
        return $response;
    }

    public function updateField(UserRequest $request, User $user)
    {
        $response = $this->userServices->updateField($request, $user);
        return $response;
    }

    public function updateImageUser(PostRequest $request, $optionImage = 0)
    {
        //Solicitamos el servicio de los posts,
        //debido a que para crear una imagen
        //esta debe estar contenida en un post
        $response = $this->postServices->controlProcessPost($request, $optionImage);
        return $response;
    }

    public function destroy(User $user)
    {
        $response = $this->userServices->destroy($user);
        return $response;
    }

    public function findUsersToRecommend(){
        $response = $this->userServices->findUsersToRecommend();
        return $response;
    }

    public function findAllFriends(User $user){
        $response = $this->userServices->findAllFriends($user);
        return $response;
    }

    public function findAllMyRequestFriend()
    {
        $friendRequest = 
            FriendRequest::where('sender', Auth::user()->id)
            ->where('status', FriendRequest::PENDING);

        return response()->json([
            'status' => true,
            'data' => $friendRequest
        ],200);
    }

    public function sendRequestFriend(User $recipient)
    {
        //Verificar si el destinatario y el usuario logueado ya son amigos

        //Verificar si el usuario logueado ya ha realizado una solicitud al destinatario

        //Guardar el registro de la solicitud de amistad
        $request = FriendRequest::create([
            'sender' => Auth::user()->id,
            'recipient' => $recipient->id,
            'status' => FriendRequest::PENDING
        ]);

        //Enviar notificacion al usuario destinatario
        $recipient->notify(new FriendRequestNotification($request));

        return response()->json([
            'message' => 'solicitud enviada',
            'status' => true
        ],201);
    }

    public function acceptRequestFriend(FriendRequest $request)
    {
        
        //Comprobamos si el usuario que acepta la solicitud es el usuario destinario de dicha solicitud
        if(Auth::user()->id != $request->recipient) return response()->json([
            'status' => false,
            'message' => 'Not authorized'
          ],403);

        //Modifica el status de friendRequest
        $request->status = FriendRequest::ACCEPTED;
        $requestUpdated = $request->save();

        if($requestUpdated){
            //Guardar el registro de la amistad
            $friends = Friend::create([
            'sender' => $request->sender,
            'recipient' => $request->recipient
            ]);

            //Enviar notificacion de aceptacion de solicitud de amistad al usuario que envio la solicitud
            $sender = User::find($request->sender);
            $sender->notify(new AcceptFrienRequestNotification($friends)); 

            return response()->json([
                'message' => 'solicitud aceptada',
                'status' => true,
                'request' => $friends
            ],201);
        }
    }
}
