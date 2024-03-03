<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
   
    public function login(Request $request)
    {
        if(!Auth::attempt($request->only(['email','password']))){
            return response()->json([
                'status' => false,
                'message' => 'Credentials Invalid'
            ],401);
        }

        $user = User::where('email', $request->email)->first();

        return response()->json([
            'status' => true,
            'message' => 'User logged in successfully',
            'token' => $user->createToken('API TOKEN')->plainTextToken,
            'user' => $user
        ],200);
    }

    public function logout(Request $request)
    {
        $user = $request->user();
        $user->tokens()->delete();
        return response()->noContent(204);
    }
}
