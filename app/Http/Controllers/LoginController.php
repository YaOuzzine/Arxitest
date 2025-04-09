<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LoginController extends Controller
{
    public function login(Request $request){
        $request->validate([
                'email' => ['required', 'string', 'email'],
                'password'=> ['string'],
            ]);

        $credentials = $request(['email', 'password']);

        if(!Auth::attempt($credentials)){
            return response()->json([
                'message' => 'unauthorized',
            ], 401);
        }

        $user = $request()->user();

        if(!$user->hasVerifiedEmail()){
            return response()->json([
                'message' => 'Email not verified',
            ], 403);
        }

        $tokenResult = $user->createToken('Personal Access Token');
        $token = $tokenResult->token;
        $token->save();

        return response()->json([
            'accesstoken' => $tokenResult->accessToken,
            'token_type' => 'Bearer',
            'expires_at' => $tokenResult->token->expire_at->toDateTimeString()
        ]);
    }

    public function logout(Request $request){

        $request->user()->token()->revoke();

        return response()->json([
            'message' => 'Successfully logged out',
        ]);
    }

    public function user(Request $request){

        return response()->json($request->user());
    }
}
