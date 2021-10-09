<?php

namespace App\Services;

use App\Exceptions\BadRequestException;
use App\Exceptions\InvalidParameterException;
use App\Exceptions\ModelNotFoundException;
use App\Http\Resources\GroupMemberResource;
use App\Http\Resources\GroupResource;
use App\Http\Resources\ResponseResource;
use App\Models\Group;
use App\Models\User;
use Illuminate\Support\Facades\Cache;

class GroupService
{

    private $groups;

    public function __construct($groups)
    {
        $this->groups = $groups;
    }

    /**
     * @param int $id Group ID
     * @param boolean $must_own Auth User must own the group
     * 
     * @return GroupManager
     */
    public static function find($id, $must_own = false)
    {
        $cacheTagKey = 'user-' . AuthService::user()->id . '-groups';
        $cacheGroupKey = 'groups' . '-' . $id;
        $hasCache = Cache::has($cacheGroupKey);
        if ($hasCache) {
            $group = Cache::get($cacheGroupKey);
        } else $group = Group::with('members')->find($id);
        throw_if($group === null, new ModelNotFoundException('Group'));
        throw_if($must_own && $group->owner_id != auth()->user()->id, new InvalidParameterException('group id'));
        if (!$hasCache) Cache::put($cacheGroupKey, $group);
        return new GroupManager($group, $cacheTagKey, $cacheGroupKey);
    }

    /**
     * @param string $name Group Name
     * 
     * @return GroupManager
     */
    public static function make($name)
    {
        $cacheTagKey = 'user-' . AuthService::user()->id . '-groups-';
        $group = Group::create([
            'name' => $name
        ]);
        try {
            AuthService::user()->ownedGroups()->save($group);
        } catch (\Exception $e) {
            $group->forceDelete();
            throw $e;
        }
        $group = $group->refresh();

        $cacheGroupKey = 'groups' . '-' . $group->id;
        Cache::put($cacheGroupKey, $group);
        return new GroupManager($group, $cacheTagKey, $cacheGroupKey);
    }

    /**
     * Get Groups where user is in a certain position
     * 
     * @param string $position
     * @return GroupService
     */
    public static function where($position)
    {

        $cacheKey = 'user-' . AuthService::user()->id . '-groups-' . $position;
        if (Cache::has($cacheKey)) {
            return new GroupService(Cache::get($cacheKey));
        }
        switch ($position) {
            case 'owner':
                $groups = AuthService::user()->ownedGroups;
                break;
            case 'member':
                $groups = AuthService::user()->memberInGroups;
                break;
            case 'any':
                $groups = (auth()->user()->ownedGroups->concat(auth()->user()->memberInGroups))->unique('id');
                break;
            default:
                throw new InvalidParameterException('position');
        }
        Cache::put($cacheKey, $groups);
        return new GroupService($groups);
    }

    public function toResource()
    {
        return count($this->groups) != 0 ? GroupResource::collection($this->groups) : ResponseResource::make('User is not part of any groups.');
    }
}

class GroupManager
{
    private $group, $cacheTag, $cacheGroupKey;

    /**
     * @param Group $group
     */
    public function __construct($group, $cacheTag, $cacheGroupKey)
    {
        $this->group = $group;
        $this->cacheTag = $cacheTag;
        $this->cacheGroupKey = $cacheGroupKey;
    }

    public function flushCache()
    {
        Cache::forget($this->cacheTag . 'any');
        if ($this->group->owner_id === AuthService::user()->id) Cache::forget($this->cacheTag . 'owner');
        if ($this->group->owner_id !== AuthService::user()->id) Cache::forget($this->cacheTag . 'member');
        Cache::forget($this->cacheGroupKey);
        return $this;
    }

    /**
     * Update group name
     * 
     * @param string $name
     * @return GroupManager
     */
    public function update($name)
    {
        try {
            $this->group->name = $name;
            $this->group->update();
            $this->group = $this->group->refresh();
        } catch (\Exception $e) {
            throw $e;
        }
        return $this;
    }

    /**
     * Soft Delete group
     * 
     * @return GroupManager
     */
    public function delete()
    {
        try {
            $this->group->members()->detach();
            $this->group->delete();
        } catch (\Exception $e) {
            throw $e;
        }
        return $this;
    }

    /**
     * Return Members in JSON response format
     * 
     * @return \Illuminate\Http\Resources\Json\AnonymousResourceCollection
     */
    public function members()
    {
        return GroupMemberResource::collection($this->group->members);
    }

    /**
     * Add users to group
     * 
     * @param string $users User IDs JSON Array
     * @return GroupManager
     */
    public function add($users)
    {
        $users = json_decode($users);
        throw_if(count($users) == 0, new BadRequestException('No users specified.'));
        foreach ($users as $user_id) {
            $u = User::find($user_id);
            if (!$this->group->members->contains($u)) {
                $this->group->members()->save($u);
            }
        }
        return $this;
    }

    /**
     * Kick users from group
     * 
     * @param string $members JSON Aray of Users
     * @return GroupManager
     */
    public function kick($members)
    {

        $m = json_decode($members);
        throw_if(count($m) == 0, new BadRequestException('No users specified.'));
        try {
            foreach ($m as $member_id) {
                $mem = User::find($member_id);
                if ($this->group->members->contains($mem)) {
                    $this->group->members()->detach($mem);
                }
            }
        } catch (\Exception $e) {
            throw $e;
        }
        return $this;
    }

    /**
     * Remove Authenticated User from group
     * 
     * @return GroupManager
     */
    public function leave()
    {
        throw_if($this->group->owner_id === AuthService::user()->id, new InvalidParameterException('group id'));
        throw_if(!$this->group->members->contains(AuthService::user()), new InvalidParameterException('group id'));
        try {
            $this->group->members()->detach(AuthService::user());
        } catch (\Exception $e) {
            throw $e;
        }
        return $this;
    }

    /**
     * Return JSON response of successfull update
     * 
     * @return \Illuminate\Http\Resources\Json\AnonymousResourceCollection
     */
    public function successMessage()
    {
        return ResponseResource::make('Group [' . $this->group->name . '] updated successfully.');
    }

    /**
     * Return Group as JSON resource
     * 
     * @return \Illuminate\Http\Resources\Json\AnonymousResourceCollection
     */
    public function groupToResource()
    {
        return GroupResource::make($this->group);
    }
}