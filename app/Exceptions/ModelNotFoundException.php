<?php

namespace App\Exceptions;

use Exception;

class ModelNotFoundException extends Exception
{
    protected $model_name;
    public function __construct(string $model_name)
    {
        $this->model_name = $model_name;
    }

    public function render($request)
    {
        return response()->json(['error' => [
            'status' => '404',
            'message' => 'Could not find '.$this->model_name.'.'
        ]], 404);
    }
}