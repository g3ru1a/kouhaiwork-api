<?php

namespace App\Http\Controllers;

use App\Http\Requests\AddMembersRequest;
use App\Http\Requests\GroupBannerRequest;
use App\Http\Requests\GroupRequest;
use App\Http\Requests\KickMemberRequest;
use App\Http\Resources\GroupCompactResource;
use App\Http\Resources\MangaCompactResource;
use App\Models\Group;
use App\Models\Manga;
use App\Services\GroupService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Cache;

class GroupController extends Controller
{
    public function all(){
        if(Cache::has('groups-all')){
            return GroupCompactResource::collection(Cache::get('groups-all'));
        }
        $grps = Group::whereNull('deleted_at')->whereHas('chapters', function (Builder $query) {
            $query->where('uploaded', true);
        })->orderBy('created_at', 'asc')->get();
        Cache::put('groups-all', $grps);
        return GroupCompactResource::collection($grps);
    }

    public function getSeries($id)
    {
        $ck = 'group-' . $id . '-series';
        if (Cache::has($ck)) {
            return MangaCompactResource::collection(Cache::pull($ck));
        }
        $group = Group::findOrFail($id);
        $chapters = $group->chapters->groupBy('manga_id');
        $series = new Collection();
        foreach($chapters as $c){
            $series = $series->add($c[0]->manga);
        }
        if($series != null) $series = $series->unique()->sortByDesc('created_at');
        
        Cache::put($ck, $series);
        return MangaCompactResource::collection($series);
    }

    public function get($id){
        return GroupCompactResource::make(Group::findOrFail($id));
    }

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

    public function storeBanner(GroupBannerRequest $request, $id){
        return GroupService::find($id, true)->updateBanner($request)->toResourceCompact();
    }

    public function update(GroupRequest $request, $id)
    {
        return GroupService::find($id, true)->update($request->toArray())->toResourceCompact();
    }

    public function delete($id) {
        return GroupService::find($id, true)->delete();
    }
}