<?php

namespace App\Http\Controllers;

use App\Mail\ForgotPassword;
use App\Mail\TestSes;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class MailController extends Controller
{
    public function test(Request $req){
        return Mail::to('egidiufarcas@maze.ws')->send(new TestSes('Testfully'));
    }

    public function forgotPasswordRequest(Request $request){
        $this->validate($request, [
            'email' => 'required|email',
        ]);
        $user = User::where('email', $request->email)->first();
        if ($user) {
            if ($user->verified == 0) {
                return response()->json(['message' => 'Email not verified.'], 401);
            }

            $user->verify_token = sha1(time());
            $user->save();
            Mail::to($user->email)->send(new ForgotPassword($user));

            return $user;
        } else return response()->json(['message' => 'User does not exist.'], 404);
    }
}
