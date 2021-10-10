<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class UserController extends Controller
{
    public function searchAll($search)
    {
        $s = str_replace(' ', '', strtolower($search));
        $users = User::where('rank', '>=', '1')->where('name', 'LIKE', '%' . $s . '%')->get();
        return count($users) != 0 ? $users : response()->json(['message' => 'Could not find the user in our database.']);
    }

    public static function search($search, $rank)
    {
        $s = str_replace(' ', '', strtolower($search));
        if(Auth::user()){
            $users = User::where('rank', '=', $rank)->where('name', 'LIKE', '%' . $s . '%')->where('id', '!=', Auth::user()->id)->get();
        }else{
            $users = User::where('rank', '=', $rank)->where('name', 'LIKE', '%' . $s . '%')->get();
        }
        return $users;
    }

    public function searchR1($search)
    {
        $users = UserController::search($search, 1);
        return count($users) != 0 ? $users : response()->json(['message' => 'Could not find the user in our database.']);
    }

    public function searchR2($search)
    {
        $users = UserController::search($search, 2);
        return count($users) != 0 ? $users : response()->json(['message' => 'Could not find the user in our database.']);
    }

    public function searchR3($search)
    {
        $users = UserController::search($search, '3');
        return count($users) != 0 ? $users : response()->json(['message' => 'Could not find the user in our database.']);
    }
}
