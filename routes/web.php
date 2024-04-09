<?php

use App\Models\User;
use Illuminate\Auth\Events\Verified;
use Illuminate\Support\Facades\Route;
use Laravel\Fortify\Contracts\VerifyEmailResponse;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/login', function () {
    return 1;
});

// Retrieve the verification limiter configuration for verification attempts
$verificationLimiter = config('fortify.limiters.verification', '6,1');

Route::get('/auth/email/verify/{id}/{hash}', function($id,$hash){

    $user = User::find($id);

    abort_if(!$user,403);

    abort_if(!hash_equals($hash, sha1($user->getEmailForVerification())),403);

    if(!$user->hasVerifiedEmail()){
        $user->markEmailAsVerified();

        event(new Verified($user));
    }

    return app(VerifyEmailResponse::class);

})->middleware(['throttle:'.$verificationLimiter])->name('verification.verify');
