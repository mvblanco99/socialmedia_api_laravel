<?php

use App\Http\Controllers\CommentController;
use App\Http\Controllers\PostController;
use App\Http\Controllers\ReactionController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')->group(function () {
    
    //RUTAS PARA SOLICITAR INFORMACION DE USUARIO LOGUEADO
    Route::get('/user',[UserController::class, 'index']);
    Route::post('/user/updateImageUser/{optionImage}',[UserController::class, 'updateImageUser']);
    Route::put('/user/update/{user}',[UserController::class, 'updateField']);
    
    //RUTAS PARA LA GESTION DE POSTS
    Route::get('/posts/{user}',[PostController::class, 'index']);
    Route::post('/posts/store',[PostController::class, 'store']);
    Route::put('/posts/update/{post}',[PostController::class, 'update']);
    Route::delete('/posts/destroy/{post}',[PostController::class, 'destroy']);
    
    //RUTAS PARA LA GESTION DE COMENTARIOS
    Route::get('/comment/{post}',[CommentController::class, 'index']);
    Route::post('/comment/store/{idRecord}/{model}',[CommentController::class, 'store']);
    Route::put('/comment/update/{comment}',[CommentController::class, 'update']);
    Route::delete('/comment/destroy/{comment}',[CommentController::class, 'destroy']);

    //RUTAS PARA LA GESTION DE REACCIONES
    Route::post('/reaction/store/{user}/{idRecord}/{model}',[ReactionController::class, 'store']);
    Route::delete('/reaction/destroy/{reaction}',[ReactionController::class, 'destroy']);

    //RUTAS PARA LA GESTION DE AMIGOS
    Route::get('/friends/search/{user}',[UserController::class, 'findAllFriends']);
    Route::get('/friends/friendRequest',[UserController::class, 'findAllMyRequestFriend']);
    Route::get('/friends/findUsersToRecommend',[UserController::class, 'findUsersToRecommend']);
    Route::post('/friends/request/{recipient}',[UserController::class, 'sendRequestFriend']);
    Route::post('/friends/accept/{request}',[UserController::class, 'acceptRequestFriend']);
});
