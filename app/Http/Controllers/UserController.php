<?php

namespace App\Http\Controllers;

use App\Http\Requests\PostRequest;
use App\Http\Requests\UserRequest;
use App\Models\User;
use App\Services\PostServices;
use App\Services\UserRelationshipService;
use App\Services\UserServices;

class UserController extends Controller
{
    public function __construct(
        private PostServices $postServices,
        private UserServices $userServices,
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
}
