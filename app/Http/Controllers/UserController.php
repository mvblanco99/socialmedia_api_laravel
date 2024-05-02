<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdateFieldUserRequest;
use App\Http\Requests\UpdateImageUserRequest;
use App\Services\PostServices;
use App\Services\UserServices;

class UserController extends Controller
{
    private const IMAGEPROFILE = 'url_image_profile';
    private const IMAGECOVER = 'url_image_cover';

    public function __construct(
        private PostServices $postServices,
        private UserServices $userServices,
    ){}
    
    //Extraemos los datos del usuario autenticado
    public  function index()
    { 
        $response = $this->userServices->index();
        return $response;
    }

    public  function findUser($user)
    {
        $response = $this->userServices->findUser($user);
        return $response;
    }

    public function updateImageProfile(UpdateImageUserRequest $request, string $user)
    {
        $response = $this->userServices->updateImage($request, $user, self::IMAGEPROFILE);
        return $response;
    }
   
    public function updateImageCover(UpdateImageUserRequest $request, string $user)
    {
        $response = $this->userServices->updateImage($request, $user, self::IMAGECOVER);
        return $response;
    }

    public function updateField(UpdateFieldUserRequest $request, string $user)
    {
        $response = $this->userServices->updateField($request, $user);
        return $response;
    }

    public function destroy(string $user)
    {
        $response = $this->userServices->destroy($user);
        return $response;
    }

    public function getImagesUser(string $user)
    {
        $response = $this->userServices->getImagesUser($user);
        return $response;
    }
}
