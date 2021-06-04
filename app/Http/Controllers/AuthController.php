<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\AuthToken;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|max:64',
            'email' => 'required|email|unique:users',
            'password' => 'required|min:8'
        ]);
        
        if ($validator->fails()) {
            return response()->json([
                "message" => $validator->messages()
            ], 422);
        }

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

        $access_token = Str::random(64);

        AuthToken::create([
            'user_id' => $user->id,
            'access_token' => $access_token,
        ]);

        return response()->json(['user' => $user, 'access_token' => $access_token], 201);
    }

    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required'
        ]);
        
        if ($validator->fails()) {
            return response()->json([
                "message" => $validator->messages()
            ], 422);
        }

        $credentials = $request->only('email', 'password');

        if(!Auth::attempt($credentials)) {
            return response()->json([
                "message" => "This User does not exist, check your details"
            ], 400);
        }

        $auth_token = AuthToken::where('user_id', Auth::id())->first();

        if(! $auth_token) {
            $access_token = Str::random(64);
    
            $auth_token = AuthToken::create([
                'user_id' => Auth::id(),
                'access_token' => $access_token,
            ]);
        }

        return response()->json(['user' => Auth::user(), 'access_token' => $auth_token->access_token]);
    }
}
