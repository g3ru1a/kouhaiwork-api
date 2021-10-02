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

class AuthService {
    public function __construct(User $user)
    {
        $this->user = $user;
    }

    /**
     * Authenticate user and return token;
     * 
     * @param array $credentials
     * 
     * @return string
     */
    public function authenticate(array $credentials){
        $user = $this->user->findByEmail($credentials['email']);
        throw_if($user === null, new ModelNotFoundException('User'));
        throw_if($user->verified === 0, new AuthException('Email not verified.'));

        $token = auth()->attempt($credentials);
        throw_if(!$token, new AuthException('Credentials do not match.'));
        return $token;
    }

    /**
     * Save new user to the database and send email verification mail.
     * 
     * @param string $name
     * @param string $email
     * @param string $password
     * 
     * @return User
     */
    public function createUser($name, $email, $password, $skip_mail){
        try {
            $user = $this->user->create([
                'name' => $name,
                'email' => $email,
                'password' => app('hash')->make($password),
                'verify_token' => sha1(time()),
            ]);
            if($skip_mail == false){
                Mail::to($user->email)->send(new VerifyEmail($user));
            }
            return $user;
        } catch (\Exception $e) {
            throw $e;
        }
    }


    /**
     * Get user and update database to mark as verified
     * 
     * @param string $user_id
     * @param string $token
     * 
     * @return null
     */
    public function verifyUserEmail($user_id, $token){
        $user = $this->user->find($user_id);
        throw_if($user === null, new ModelNotFoundException('User'));
        throw_if($user->verify_token !== $token, new AuthException('Token does not match.'));
        try {
            $user->verify_token = '';
            $user->verified = 1;
            $user->save();
            return null;
        } catch (\Exception $e) {
            throw $e;
        }
    }

    /**
     * Get user, update password and clear verify_token
     * 
     * @param string $user_id
     * @param string $token
     * @param string $password
     * 
     * @return null
     */
    public function resetUserPassword($user_id, $token, $password){
        $user = $this->user->find($user_id);
        throw_if($user === null, new ModelNotFoundException('User'));
        throw_if($user->verify_token !== $token, new AuthException('Token does not match.'));
        try {
            $user->verify_token = '';
            $user->password = app('hash')->make($password);
            $user->save();
            return null;
        } catch (\Exception $e) {
            throw $e;
        }
    }

}
