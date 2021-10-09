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
        return GroupService::where('any')->toResource();
    }

    public function getWhere($position)
    {
        return GroupService::where($position)->toResource();
    }

    public function getMembers($id) {
        return GroupService::find($id, true)->members();
    }

    public function kickMembers(KickMemberRequest $request, $id){
        return GroupService::find($id, true)->kick($request->members)->flushCache()->successMessage();
    }

    public function leaveGroup($id) {
        return GroupService::find($id)->leave()->flushCache()->groupToResource();
    }

    public function addMembers(AddMembersRequest $request, $id) {
        return GroupService::find($id, true)->add($request->users)->flushCache()->successMessage();
    }

    public function store(GroupRequest $request){
        return GroupService::make($request->name)->flushCache()->groupToResource();
    }

    public function update(GroupRequest $request, $id)
    {
        return GroupService::find($id, true)->update($request->name)->flushCache()->groupToResource();
    }

    public function delete($id) {
        return GroupService::find($id, true)->delete()->flushCache()->successMessage();
    }
}

