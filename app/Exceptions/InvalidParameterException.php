<?php

namespace App\Exceptions;

use Exception;

class InvalidParameterException extends Exception
{
    protected $message;
    public function __construct(string $message)
    {
        $this->message = $message;
    }

    public function render($request)
    {
        return response()->json(['error' => [
            'status' => '406',
            'message' => 'Route parameter ['. $this->message .'] is invalid.'
        ]], 406);
    }
}