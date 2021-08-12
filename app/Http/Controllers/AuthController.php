<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;

class AuthController extends Controller
{
    public function login(Request $request){
        $this->validate($request, [
            'email' => 'required|email',
            'password' => 'required|string'
        ]);

        $credentials = request(['email', 'password']);
        
        if(!$token = auth()->attempt($credentials)){
            return response()->json(['error', 'Unauthorized'], 401);
        }
        
        return $this->respondWithToken($token);
    }

    public function register(Request $request){
        $this->validate($request, [
            'name' => 'required|string',
            'email' => 'required|email|unique:users',
            'password' => 'required|string'
        ]);

        try {
            $user = new User();
            $user->name = $request->name;
            $user->email = $request->email;
            $user->password = app('hash')->make($request->password);
            if($user->save()){
                return $this->login($request);
            }
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => $e.getMessage()]);
        }
    }

    public function logout(){
        auth()->logout();
        return response()->json(['message' => 'Successfully Logged Out']);
    }

    protected function respondWithToken($token){
        return response()->json([
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => auth()->factory()->getTTL() * 60
        ]);
    }
}
