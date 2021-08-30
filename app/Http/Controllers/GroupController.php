<?php

namespace App\Http\Controllers;

use App\Models\Group;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class GroupController extends Controller
{
    public function search($search)
    {
        $s = str_replace('_', ' ', strtolower($search));
        $group = Group::where('name', 'LIKE', '%' . $s . '%')->get();
        return count($group) != 0 ? $group : response()->json(['message' => 'Could not find the specified group in our database.']);
    }

    public function getUserGroups(){
        $group = Auth::user()->ownedGroups->concat(Auth::user()->memberInGroups);
        $group = $group->unique('id');
        return count($group) != 0 ? $group : response()->json(['message' => 'User is not part of any groups.']);
    }
    public function getUserOwnedGroups()
    {
        $group = Auth::user()->ownedGroups;
        return count($group) != 0 ? $group : response()->json(['message' => 'User is not part of any groups.']);
    }
    public function getUserMemberGroups()
    {
        $group = Auth::user()->memberInGroups;
        return count($group) != 0 ? $group : response()->json(['message' => 'User is not part of any groups.']);
    }

    public function leaveGroup($id) {
        try {
            if (Auth::user()->memberInGroups()->detach($id)) {
                return Group::find($id);
            }
        } catch (\Throwable $th) {
            return $th;
        }
    }

    public function addMembers(Request $request) {
        $this->validate($request, [
            'group_id' => 'required',
            'users' => 'required',
        ]);

        $group = Group::find($request->group_id);
        if($group){
            $users = json_decode($request->users);
            foreach ($users as $user) {
                $u = User::find($user->id);
                $group->members()->save($u);
            }
            return response()->json(['message' => 'Successfully added to group.']);
        }else return response()->json(['message' => 'Could not find group.']);
    }

    public function create(Request $request){
        $this->validate($request, [
            'name' => 'required|string|unique:groups',
        ]);
        $g = new Group();
        $g->name = $request->name;
        Auth::user()->ownedGroups()->save($g);
        $g->refresh();
        return $g;
    }

    public function update(Request $request, $id){
        $g = Group::find($id);
        if ($g && $g->owner_id == Auth::user()->id) {
            $this->validate($request, [
                'name' => 'required|string|unique:groups',
            ]);
            $g->name = $request->name;
            $g->update();
            return $g;
        }
        return response()->json(['error' => '404', 'message' => 'Group not found.']);
    }

    public function delete($id) {
        $g = Group::find($id);
        if($g && $g->owner_id == Auth::user()->id){
            $g->delete();
            return $g;
        }
        return response()->json(['error' => '404', 'message' => 'Group not found.']);
    }
}
