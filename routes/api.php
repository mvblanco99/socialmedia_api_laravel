<?php

use App\Http\Controllers\CommentController;
use App\Http\Controllers\FriendController;
use App\Http\Controllers\PostController;
use App\Http\Controllers\ReactionController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;
use Laravel\Fortify\Http\Controllers\AuthenticatedSessionController;
use Laravel\Fortify\Http\Controllers\PasswordResetLinkController;
use Laravel\Fortify\Http\Controllers\RegisteredUserController;

Route::group(['middleware' => 'auth:sanctum'], function() {

    // Authentication routes 
    Route::prefix('auth')->group(function () {

        // Retrieve the verification limiter configuration for verification attempts
        $verificationLimiter = config('fortify.limiters.verification', '6,1');

        Route::withoutMiddleware('auth:sanctum')->group(function () {
            // Retrieve the limiter configuration for login attempts
            $limiter = config('fortify.limiters.login');

            // Route for user login
            Route::post('/login', [AuthenticatedSessionController::class, 'store'])
                ->middleware(array_filter([
                    'guest:'.config('fortify.guard'),  // Only guests (non-authenticated users) are allowed
                    $limiter ? 'throttle:'.$limiter : null,  // Throttle login attempts if limiter is configured
                ]));

            // Route for user registration
            Route::post('/register', [RegisteredUserController::class, 'store'])
                ->middleware('guest:'.config('fortify.guard'));  // Only guests (non-authenticated users) are allowed

            // Route for initiating password reset
            Route::post('/forgot-password', [PasswordResetLinkController::class, 'store'])
                ->middleware('guest:'.config('fortify.guard'))  // Only guests (non-authenticated users) are allowed
                ->name('password.email');  // Name for the route
        });
    });

    //RUTAS PARA SOLICITAR INFORMACION DE USUARIO LOGUEADO
    Route::get('/user',[UserController::class, 'index']);
    Route::get('/user/view/{user}/images',);
    Route::post('/user/updateImageUser/{optionImage}',[UserController::class, 'updateImageUser']);
    Route::put('/user/{user}/update',[UserController::class, 'updateField']);

    //RUTAS PARA LA GESTION DE POSTS
    Route::get('/posts/{user}',[PostController::class, 'index']);
    Route::post('/posts/store',[PostController::class, 'store']);
    Route::put('/posts/{post}/update/',[PostController::class, 'update']);
    Route::delete('/posts/{post}/destroy',[PostController::class, 'destroy']);
    
    //RUTAS PARA LA GESTION DE COMENTARIOS
    Route::get('/post/{id_post}/comments',[CommentController::class, 'index']);
    Route::post('/post/comment/store/{idRecord}/{model}',[CommentController::class, 'store']);
    Route::put('/post/comment/{id_comment}/update',[CommentController::class, 'update']);
    Route::delete('/post/comment/{id_comment}/destroy',[CommentController::class, 'destroy']);

    //RUTAS PARA LA GESTION DE REACCIONES
    Route::post('/reaction/store/{user}/{idRecord}/{model}',[ReactionController::class, 'store']);
    Route::delete('/reaction/{reaction}/destroy',[ReactionController::class, 'destroy']);

    //RUTAS PARA LA GESTION DE AMIGOS
    Route::get('/friends/{user}/findFriends',[FriendController::class, 'findFriends']);
    Route::get('/friends/friendRequest',[FriendController::class, 'findAllMyRequestFriend']);
    Route::get('/friends/findUsersToRecommend',[FriendController::class, 'findUsersToRecommend']);
    Route::post('/friends/{recipient}/request',[FriendController::class, 'sendRequestFriend']);
    Route::post('/friends/{friendRequest}/accept',[FriendController::class, 'acceptRequestFriend']);
    Route::delete('/friends/{user}/destroy',[FriendController::class, 'destroyFriendshipRelationship']);

    //RUTAS PARA LA GESTION DE NOTIFICACIONES

});