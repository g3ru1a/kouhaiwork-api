<?php

namespace App\Services;

use App\Exceptions\ModelNotFoundException;
use App\Http\Resources\ResponseResource;
use Exception;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Cache;
use PhpParser\ErrorHandler\Throwing;

abstract class BaseService
{
    /**
     * @return Model
     */
    abstract public static function getModelClass();

    /**
     * @param int|string $id Model ID
     * @return string
     */
    abstract public static function getCacheKeyStatic($id);

    /**
     * @return JsonResource
     */
    abstract public static function getResourceCompactClass();

    /**
     * @return JsonResource
     */
    abstract public static function getResourceClass();

    /**
     * Function called after make/update/delete
     */
    public function postDataChanges(){
        Cache::forget(static::getCacheKeyStatic($this->getSingleModel()->id));
    }

    /**
     * Extend this function if you want to add extra functionality to the find method
     * Example: Returning: ['cover'] will result in this: Model::with(['cover'])->find($id);
     */
    public static function findWithOptions()
    {
        return null;
    }

    /**
     * Run extra validation for the find method. Make sure to include
     * parent::findActionValidation() to have null check.
     */
    public static function findActionValidation($model)
    {
        throw_if($model === null, new ModelNotFoundException(static::getModelClassName()));
    }

    private $singleModel, $collectionModel;

    /**
     * @return string
     */
    private static function getModelClassName(){
        $modelName = explode('\\', static::getModelClass());
        return end($modelName);
    }

    /**
     * @return Model
     */
    public function getSingleModel(){
        throw_if($this->singleModel === null, new ModelNotFoundException(static::getModelClassName()));
        return $this->singleModel;
    }

    /**
     * @param Model
     */
    public function setSingleModel($instance){
        $modelClass = static::getModelClass();
        throw_if(!($instance instanceof $modelClass), new Exception('Cannot set single model instance because given instance is null', 422));
        $this->singleModel = $instance;
    }

    /**
     * @param Model|Collection $singleOrCollectionModel
     */
    public function __construct($singleOrCollectionModel)
    {
        if($singleOrCollectionModel instanceof Collection){
            $this->collectionModel = $singleOrCollectionModel;
        }else $this->singleModel = $singleOrCollectionModel;
    }

    /**
     * @param int $id Model ID
     * @return static
     */
    public static function find($id)
    {
        //Get the cachekey
        $cacheKey = static::getCacheKeyStatic($id);
        //Check Cache availability
        if(Cache::has($cacheKey)){
            return new static(Cache::pull($cacheKey));
        }
        //If theres no cache, try and find the model
        $instance = static::getModelClass()::where('deleted_at', null);
        //If theres any extra options defined, apply them
        if(static::findWithOptions() != null) $instance = $instance->with(static::findWithOptions());
        $instance = $instance->find($id);
        //Apply Error checks
        try {
            static::findActionValidation($instance);
        } catch (\Exception $e) {
            throw $e;
        }
        //Place in cache and return;
        Cache::put(static::getCacheKeyStatic($id), $instance);
        return new static($instance);
    }

    /**
     * @param array $dataKeyValueArray
     * @return static
     */
    public static function make($dataKeyValueArray, $preventPostDataChanges = false){
        //Try and create model instance
        try {
            $instance = static::getModelClass()::create($dataKeyValueArray);
        } catch (\Exception $e) {
            throw $e;
        }
        //Create Service instance
        $staticInstance = new static($instance);
        //Run post data changes actions if required
        if($preventPostDataChanges == false) $staticInstance->postDataChanges();
        return $staticInstance;
    }

    /**
     * @param array $dataKeyValueArray
     * @return self
     */
    public function update($dataKeyValueArray){
        try {
            $this->singleModel->fill($dataKeyValueArray)->save();
        } catch (\Exception $e) {
            throw $e;
        }
        $this->singleModel = $this->singleModel->refresh();
        $this->postDataChanges();
        return $this;
    }

    /**
     * Delete Single model or whole collection
     * @return ResponseResource
     */
    public function delete(){
        if($this->singleModel) $this->deleteSingle();
        else if($this->collectionModel) $this->deleteCollection();
        $this->postDataChanges();
        return ResponseResource::make('Deleted.');
    }

    private function deleteSingle(){
        $this->singleModel->delete();
    }

    private function deleteCollection(){
        foreach($this->collectionModel as $model){
            $model->delete();
        }
    }

    /**
     * Compact response
     * @param JsonResource $resourceClass
     */
    public function toResourceCompact(){
        $resourceClass = static::getResourceCompactClass();
        if($this->singleModel){
            return $this->toResourceCompactSingle($resourceClass);
        }
        if($this->collectionModel){
            return $this->toResourceCompactCollection($resourceClass);
        }
    }
    private function toResourceCompactSingle($resourceClass){
        return $resourceClass::make($this->singleModel);
    }
    private function toResourceCompactCollection($resourceClass)
    {
        return $resourceClass::collection($this->collectionModel);
    }

    /**
     * Full size response
     * @param JsonResource $resourceClass
     */
    public function toResource()
    {
        $resourceClass = static::getResourceClass();
        if ($this->singleModel) {
            return $this->toResourceSingle($resourceClass);
        }
        if ($this->collectionModel) {
            return $this->toResourceCollection($resourceClass);
        }
    }
    private function toResourceSingle($resourceClass)
    {
        return $resourceClass::make($this->singleModel);
    }
    private function toResourceCollection($resourceClass)
    {
        return $resourceClass::collection($this->collectionModel);
    }
}
