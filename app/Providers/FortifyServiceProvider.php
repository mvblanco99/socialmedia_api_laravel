<?php

namespace App\Providers;

use App\Actions\Fortify\CreateNewUser;
use App\Actions\Fortify\ResetUserPassword;
use App\Actions\Fortify\UpdateUserPassword;
use App\Actions\Fortify\UpdateUserProfileInformation;
use App\Models\User;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;
use Laravel\Fortify\Fortify;
use Laravel\Fortify\Contracts\{EmailVerificationNotificationSentResponse, LoginResponse, LogoutResponse, RegisterResponse, VerifyEmailResponse};

class FortifyServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {

        $this->app->instance(LoginResponse::class, new class implements LoginResponse {
            public function toResponse($request)
            {
                if($request->wantsJson()) {
                    $user = User::where('email', $request->email)->first();

                    return response()->json([
                        "message" => "You are successfully logged in",
                        "token" => $user->createToken($request->email)->plainTextToken,
                    ], 200);
                }
                return redirect()->intended(Fortify::redirects('login'));
            }
        });
     
        $this->app->instance(RegisterResponse::class, new class implements RegisterResponse {
            public function toResponse($request)
            {
                return $request->wantsJson()
                    ? response()->json([
                        'message' => 'Registration successful, verify your email address',
                        ], 200)
                    : redirect()->intended(Fortify::redirects('register'));
            }
        });

        $this->app->instance(VerifyEmailResponse::class, new class implements VerifyEmailResponse {
            public function toResponse($request)
            {
                return $request->wantsJson()
                    ? response()->json([
                        'message' => 'Su correo ha sido verificado',
                        ], 200)
                    : redirect()->intended(Fortify::redirects('register'));
            }
        });

        $this->app->instance(EmailVerificationNotificationSentResponse::class, new class implements EmailVerificationNotificationSentResponse{

            public function toResponse($request)
            {
                return $request->wantsJson()
                    ? response()->json([
                        'message' => 'Su ha reenviado correo de confirmación',
                        ], 200)
                    : redirect()->intended(Fortify::redirects('home'));

            }
        });

        $this->app->instance(LogoutResponse::class, new class implements LogoutResponse{

            public function toResponse($request)
            {
                return response()->noContent(204);
            }
        });

    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Fortify::createUsersUsing(CreateNewUser::class);
        Fortify::updateUserProfileInformationUsing(UpdateUserProfileInformation::class);
        Fortify::updateUserPasswordsUsing(UpdateUserPassword::class);
        Fortify::resetUserPasswordsUsing(ResetUserPassword::class);

        RateLimiter::for('login', function (Request $request) {
            $throttleKey = Str::transliterate(Str::lower($request->input(Fortify::username())).'|'.$request->ip());

            return Limit::perMinute(5)->by($throttleKey);
        });

        RateLimiter::for('two-factor', function (Request $request) {
            return Limit::perMinute(5)->by($request->session()->get('login.id'));
        });
    }
}
