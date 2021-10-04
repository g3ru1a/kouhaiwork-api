<?php

namespace App\Http\Controllers;

use App\Models\Group;
use App\Models\User;
use Illuminate\Validation\Rule;
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

    public function index(){
        $group = Auth::user()->ownedGroups->concat(Auth::user()->memberInGroups);
        $group = $group->unique('id');
        return count($group) != 0 ? $group : response()->json(['message' => 'User is not part of any groups.']);
    }

    public function getWhere($position)
    {
        if($position === 'owner'){
            $group = Auth::user()->ownedGroups;
            return count($group) != 0 ? $group : response()->json(['message' => 'User is not part of any groups.']);
        }
        if($position === 'member'){
            $group = Auth::user()->memberInGroups;
            return count($group) != 0 ? $group : response()->json(['message' => 'User is not part of any groups.']);
        }
    }

    public function getMembers($id) {
        $group = Group::with('members')->find($id);
        if(!$group || $group->owner_id != Auth::user()->id) {
            return response()->json(['message' => 'Group not found.'], 422);
        }
        return $group;
    }

    public function kickMember(Request $request, $id){
        $this->validate($request, [
            'memberID' => 'required'
        ]);
        $member = User::find($request->memberID);
        if(!$member) return response()->json(['message' => 'Member not found.'], 422);

        $group = Group::find($request->groupID);
        if (!$group) return response()->json(['message' => 'Group not found.'], 422);

        if($group->owner_id == Auth::user()->id){
            if($group->members()->detach($member)){
                return response()->json(['message' => 'Member kicked successfully.']);
            }else {
                return response()->json(['message' => 'Could not kick member.'], 422);
            }
        }else return response()->json(['message' => 'You are not group owner+.'], 422);
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

    public function addMembers(Request $request, $id) {
        $this->validate($request, [
            'users' => 'required',
        ]);

        $group = Group::find($request->group_id);
        if($group){
            $users = json_decode($request->users);
            if(count($users) == 0) return response()->json(['message' => 'No users specified.'], 422);
            foreach ($users as $user) {
                $u = User::find($user->id);
                if(!$group->members->contains($u)) {
                    $group->members()->save($u);
                }
            }
            return response()->json(['message' => 'Successfully added to group.']);
        }else return response()->json(['message' => 'Could not find group.'], 422);
    }

    public function store(Request $request){
        $this->validate($request, [
            'name' => [
                'required', 'string', Rule::unique('groups')->whereNull('deleted_at')
            ],
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
