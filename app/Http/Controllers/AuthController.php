<?php

namespace App\Http\Controllers;

use App\Exceptions\AuthException;
use App\Exceptions\ModelNotFoundException;
use App\Http\Requests\ForgotPasswordRequest;
use App\Http\Requests\LoginRequest;
use App\Http\Requests\RegisterRequest;
use App\Http\Requests\ResetPasswordRequest;
use App\Mail\ForgotPassword;
use App\Mail\VerifyEmail;
use App\Models\User;
use App\Services\AuthService;
use Illuminate\Support\Facades\Mail;

class AuthController extends Controller
{
    public function __construct(User $user, AuthService $auth_service)
    {
        $this->user = $user;
        $this->service = $auth_service;
    }

    public function login(LoginRequest $request){
        $credentials = request(['email', 'password']);
        $token = $this->service->authenticate($credentials);
        return $this->respondWithToken($token);
    }

    public function register(RegisterRequest $request){
        $skip = $request->skip_mail ? $request->skip_mail : false;
        $user = $this->service->createUser($request->name, $request->email, $request->password, $skip);
        return response()->json(['data' => ['user' => $user]]);
    }

    public function logout(){
        try {
            auth()->logout();
            return response()->json(['data' => ['message' => 'Successfully Logged Out.']]);
        } catch (\Exception $e) {
            throw $e;
        }
    }

    public function check(){
        return response()->json(['data' => ['message' => 'Logged In']]);
    }

    public function verifyEmail($user_id, $token){
        $this->service->verifyUserEmail($user_id, $token);
        return response()->json(['data' => ['message' => 'Account Verified.']]);
    }

    public function resetPassword(ResetPasswordRequest $request)
    {
        $this->service->resetUserPassword($request->user_id, $request->token, $request->password);
        return response()->json(['data' => ['message' => 'Password Reset.']]);
    }

    public function forgotPasswordRequest(ForgotPasswordRequest $request)
    {
        $user = User::where('email', $request->email)->first();
        throw_if($user === null, new ModelNotFoundException('User'));
        throw_if($user->verified === 0, new AuthException('Email not verified.'));
        $user->verify_token = sha1(time());
        $user->save();
        if($request->skip_email != true){
            Mail::to($user->email)->send(new ForgotPassword($user));
        }
        return response()->json(['data' => ['message' => 'Password Reset Requested.']]);;
    }

    protected function respondWithToken($token){
        return response()->json([
            'data' => [
                'access_token' => $token,
                'token_type' => 'bearer',
                'expires_in' => auth()->factory()->getTTL() * 60,
                'user' => auth()->user()
            ]
        ]);
    }
}
