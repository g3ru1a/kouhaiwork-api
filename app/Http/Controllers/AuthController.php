<?php

namespace App\Http\Controllers;

use App\Mail\VerifyEmail;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Mail;

class AuthController extends Controller
{
    public function login(Request $request){
        $this->validate($request, [
            'email' => 'required|email',
            'password' => 'required|string'
        ]);

        $credentials = request(['email', 'password']);
        $user = User::where('email', $request->email)->first();
        if($user){
            if($user->verified == 0 ) {
                return response()->json(['message' => 'Email not verified.'], 401);
            }
            if (!$token = auth()->attempt($credentials)) {
                return response()->json(['message' => 'Credentials do not match.'], 401);
            }

            return $this->respondWithToken($token);
        } else return response()->json(['message' => 'User does not exist.'], 404);
    }

    public function register(Request $request){
        $messages = [
            'password.regex' => 'Password must contain at least 1 uppercase letter and 1 number or special character (@.,!$#%).',
        ];
        $this->validate($request, [
            'name' => 'required|string|unique:users',
            'email' => 'required|email|unique:users',
            'password' => 'required|confirmed|min:8|regex:/^.*(?=.{3,})(?=.*[a-z])(?=.*[A-Z])(?=.*[0-9])(?=.*[\d\x])(?=.*[@.,!$#%]).*$/'
        ], $messages);

        try {
            $user = new User();
            $user->name = $request->name;
            $user->email = $request->email;
            $user->password = app('hash')->make($request->password);
            $user->verify_token = sha1(time());
            if($user->save()){
                Mail::to($user->email)->send(new VerifyEmail($user));
                return $user;
            }
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => $e]);
        }
    }

    public function logout(){
        auth()->logout();
        return response()->json(['message' => 'Successfully Logged Out']);
    }

    public function check(){
        return response()->json(['message' => 'Logged In']);
    }

    public function verifyEmail($user_id, $token){
        $user = User::find($user_id);
        if($user){
            if($user->verify_token == $token) {
                $user->verified = 1;
                $user->verify_token = '';
                $user->save();
                return response()->json(['status' => 'success', 'message' => 'Account Verified.']);
            } else return response()->json(['status' => 'error', 'message' => 'Token does not match.'], 422);
        }else return response()->json(['status' => 'error', 'message' => 'User not found.'], 422);
    }

    public function resetPassword(Request $request)
    {
        $messages = [
            'password.regex' => 'Password must contain at least 1 uppercase letter and 1 number or special character (@.,!$#%).',
        ];
        $this->validate($request, [
            'user_id' => 'required',
            'token' => 'required|string',
            'password' => 'required|confirmed|min:8|regex:/^.*(?=.{3,})(?=.*[a-z])(?=.*[A-Z])(?=.*[0-9])(?=.*[\d\x])(?=.*[@.,!$#%]).*$/'
        ], $messages);
        $user = User::find($request->user_id);
        if ($user) {
            if ($user->verify_token == $request->token) {
                $user->verify_token = '';
                $user->password = app('hash')->make($request->password);
                $user->save();
                return response()->json(['status' => 'success', 'message' => 'Password Reset.']);
            } else return response()->json(['status' => 'error', 'message' => 'Token does not match.'], 422);
        } else return response()->json(['status' => 'error', 'message' => 'User not found.'], 422);
    }

    protected function respondWithToken($token){
        return response()->json([
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => auth()->factory()->getTTL() * 60,
            'user' => auth()->user()
        ]);
    }
}
