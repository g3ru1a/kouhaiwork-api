<?php

namespace App\Services;

use App\Exceptions\AuthException;
use App\Exceptions\ModelNotFoundException;
use App\Mail\VerifyEmail;
use App\Models\User;
use Illuminate\Support\Facades\Mail;

class AuthService
{
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
    public function authenticate(array $credentials)
    {
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
    public function createUser($name, $email, $password, $skip_mail)
    {
        try {
            $user = $this->user->create([
                'name' => $name,
                'email' => $email,
                'password' => app('hash')->make($password),
                'verify_token' => sha1(time()),
            ]);
            if ($skip_mail == false) {
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
    public function verifyUserEmail($user_id, $token)
    {
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
    public function resetUserPassword($user_id, $token, $password)
    {
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

    public static function user(): User
    {
        return auth()->user();
    }
}