<?php

namespace App\Services;


use App\Http\Resources\GroupCompactResource;
use App\Http\Resources\GroupMemberResource;
use App\Http\Resources\GroupResource;
use App\Models\Group;
use App\Models\User;
use App\Services\AuthService;
use Illuminate\Support\Facades\Cache;
use App\Exceptions\BadRequestException;
use App\Exceptions\InvalidParameterException;
use App\Http\Controllers\MediaController;

class GroupService extends BaseService
{

    public static function getCacheKeyStatic($id)
    {
        return 'group-' . $id;
    }

    public static function getModelClass()
    {
        return Group::class;
    }

    public static function getResourceClass()
    {
        return GroupResource::class;
    }

    public static function getResourceCompactClass()
    {
        return GroupCompactResource::class;
    }

    public function postDataChanges()
    {
        parent::postDataChanges();
        $user = AuthService::user();
        Cache::forget('user-' . $user->id . '-groups-any');
        if ($this->getSingleModel() && $this->getSingleModel()->owner_id === $user->id) {
            Cache::forget('user-' . $user->id . '-groups-owner');
        }
        if ($this->getSingleModel() && $this->getSingleModel()->owner_id !== $user->id) {
            Cache::forget('user-' . $user->id . '-groups-member');
        }

        Cache::forget('groups-all');
        Cache::forget('group-'. $this->getSingleModel()->id.'-series');
    }

    /**
     * Get Groups where user is in a certain position
     * 
     * @param string $position
     * @return GroupService
     */
    public static function where($position)
    {
        $user = AuthService::user();
        $cacheKey = 'user-' . $user->id . '-groups-' . $position;
        if (Cache::has($cacheKey)) {
            return new self(Cache::get($cacheKey));
        }
        switch ($position) {
            case 'owner':
                $groups = $user->ownedGroups;
                break;
            case 'member':
                $groups = $user->memberInGroups;
                break;
            case 'any':
                $groups = ($user->ownedGroups->concat($user->memberInGroups))->unique('id');
                break;
            default:
                throw new InvalidParameterException('position');
        }
        Cache::put($cacheKey, $groups);
        return new self($groups);
    }

    /**
     * Extended find method, added ownership check
     * @param int $id
     * @param boolean $mustBeOwner
     */
    public static function find($id, $mustBeOwner = false)
    {
        $instance = parent::find($id);
        if ($mustBeOwner) {
            throw_if(
                $instance->getSingleModel()->owner_id !== AuthService::user()->id,
                new InvalidParameterException('group id')
            );
        } else {
            throw_if(
                $instance->getSingleModel()->owner_id === AuthService::user()->id,
                new InvalidParameterException('group id')
            );
        }
        return $instance;
    }

    public function updateBanner($request){
        $modelInstance = $this->getSingleModel();
        $modelInstance->banner()->delete();
        $media = MediaController::upload($request->file('banner'), 'banners/' . $modelInstance->id);
        $modelInstance->banner()->save($media);
        $this->setSingleModel($modelInstance);
        $this->postDataChanges();
        return $this;
    }

    /**
     * Extended delete method, added member detaching
     */
    public function delete()
    {
        $this->getSingleModel()->members()->detach();
        parent::delete();
    }

    /**
     * Extended make method, added owner association for the authenticated user
     * 
     * @param string $name Group Name
     * 
     * @return GroupService
     */
    public static function make($dataKeyValueArray, $preventPostDataChanges = true)
    {
        $instance = parent::make($dataKeyValueArray, $preventPostDataChanges);
        $modelInstance = $instance->getSingleModel();
        try {
            AuthService::user()->ownedGroups()->save($modelInstance);
        } catch (\Exception $e) {
            $instance->delete();
            throw $e;
        }
        $instance->setSingleModel($modelInstance->refresh());
        $instance->postDataChanges();
        return $instance;
    }

    /**
     * Get members as JSON Resource
     * @return JsonResource
     */
    public function members()
    {
        return GroupMemberResource::collection($this->getSingleModel()->members);
    }

    /**
     * Add an array of users as members to the group
     * 
     * @param string $users JSON Array of user IDs
     * @return self
     */
    public function addMembers($users)
    {
        $users = json_decode($users);
        throw_if(count($users) == 0, new BadRequestException('No users specified.'));
        $modelInstance = $this->getSingleModel();
        foreach ($users as $user_id) {
            $user = User::find($user_id);
            if (!$modelInstance->members->contains($user)) {
                $modelInstance->members()->attach($user);
            }
        }
        $this->setSingleModel($modelInstance->refresh());
        $this->postDataChanges();
        return $this;
    }

    /**
     * Kick an array of members from the group
     * 
     * @param string $members JSON Array of member IDs
     * @return self
     */
    public function kickMembers($members)
    {
        $members = json_decode($members);
        throw_if(count($members) == 0, new BadRequestException('No members specified.'));
        $modelInstance = $this->getSingleModel();
        foreach ($members as $member_id) {
            $member = User::find($member_id);
            if ($modelInstance->members->contains($member)) {
                $modelInstance->members()->detach($member);
            }
        }
        $this->setSingleModel($modelInstance->refresh());
        $this->postDataChanges();
        return $this;
    }

    /**
     * Have the authenticated user leave the group
     * 
     * @return self
     */
    public function leave()
    {
        $modelInstance = $this->getSingleModel();
        throw_if(
            !$modelInstance->members->contains(AuthService::user()),
            new InvalidParameterException('group id')
        );
        try {
            $modelInstance->members()->detach(AuthService::user());
        } catch (\Exception $e) {
            throw $e;
        }
        $this->setSingleModel($modelInstance->refresh());
        $this->postDataChanges();
        return $this;
    }
}