<?php

namespace App\Http\Controllers;

use App\Http\Requests\AddMembersRequest;
use App\Http\Requests\GroupRequest;
use App\Http\Requests\KickMemberRequest;
use App\Services\GroupService;

class GroupController extends Controller
{
    public function index()
    {
        return GroupService::where('any')->toResourceCompact();
    }

    public function getWhere($position)
    {
        return GroupService::where($position)->toResourceCompact();
    }

    public function getMembers($id) {
        return GroupService::find($id, true)->members();
    }

    public function kickMembers(KickMemberRequest $request, $id){
        return GroupService::find($id, true)->kickMembers($request->members)->toResource();
    }

    public function leaveGroup($id) {
        return GroupService::find($id)->leave()->toResourceCompact();
    }

    public function addMembers(AddMembersRequest $request, $id) {
        return GroupService::find($id, true)->addMembers($request->users)->toResource();
    }

    public function store(GroupRequest $request){
        return GroupService::make($request->toArray())->toResourceCompact();
    }

    public function update(GroupRequest $request, $id)
    {
        return GroupService::find($id, true)->update($request->toArray())->toResourceCompact();
    }

    public function delete($id) {
        return GroupService::find($id, true)->delete();
    }
}